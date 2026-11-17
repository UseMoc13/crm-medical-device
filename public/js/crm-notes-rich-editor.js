/* A7.4.10.3 — Safe in-place links for CRM note mentions.
 * Storage stays plain text with UUID tokens; the EDITOR displays actual links.
 * No arbitrary pasted HTML, no contenteditable HTML sent to the backend.
 */
(function (root) {
    'use strict';
    if (root.CrmNotesRichEditor) return;
    const UUID = '[0-9a-fA-F-]{36}';
    const TOKEN = new RegExp('@\\[(Activity|TO-DO|Calendar):([^\\]\\r\\n]{1,120})\\]\\((activity|todo|calendar):(' + UUID + ')\\)', 'g');
    const supported = {activity:'activities', todo:'todos', calendar:'calendar/events'};
    const displayType = {activity:'Activity', todo:'TO-DO', calendar:'Calendar'};
    const uuidPattern = /^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/;
    const cleanLabel = (str) => String(str || '').replace(/[\]\r\n]/g, ' ').slice(0, 100).trim() || 'Untitled';
    const rawToken = (type, title, id) => `@[${displayType[type]}:${cleanLabel(title)}](${type}:${id})`;

    function makeLink(type, title, id, token) {
        const link = document.createElement('a');
        link.className = 'notes-mention-link notes-inline-mention';
        link.textContent = '@' + cleanLabel(title);
        link.title = `Open ${displayType[type]}: ${title} (new tab)`;
        link.href = `${root.location.origin}/${supported[type]}/${encodeURIComponent(id)}`;
        link.target = '_blank';
        link.rel = 'noopener noreferrer';
        link.setAttribute('contenteditable','false');
        link.setAttribute('data-note-token', token);
        link.setAttribute('data-note-type', type);
        return link;
    }

    function editor(node, onChange) {
        if (!node) throw new Error('Missing notes editor element');
        node.contentEditable = 'true';
        node.setAttribute('role','textbox');
        node.setAttribute('aria-multiline','true');
        node.setAttribute('spellcheck','true');

        // Only text nodes and explicitly created mention anchors are accepted.
        function setValue(value) {
            const body = String(value || '');
            const fragment = document.createDocumentFragment();
            let index = 0;
            TOKEN.lastIndex = 0;
            for (const match of body.matchAll(TOKEN)) {
                const [token, display, title, type, id] = match;
                if (match.index > index) fragment.append(document.createTextNode(body.slice(index, match.index)));
                const valid = (display === displayType[type] && uuidPattern.test(id));
                fragment.append(valid ? makeLink(type,title,id,token) : document.createTextNode(token));
                index = match.index + token.length;
            }
            fragment.append(document.createTextNode(body.slice(index)));
            node.replaceChildren(fragment);
        }

        function getValue() {
            function walk(element) {
                if (element.nodeType === Node.TEXT_NODE) return element.nodeValue || '';
                if (element.nodeType !== Node.ELEMENT_NODE) return '';
                if (element.hasAttribute('data-note-token')) return element.getAttribute('data-note-token') || '';
                if (element.tagName === 'BR') return '\n';
                const children = Array.from(element.childNodes).map(walk).join('');
                return ['DIV','P'].includes(element.tagName) ? children + '\n' : children;
            }
            return Array.from(node.childNodes).map(walk).join('').replace(/\r\n/g,'\n');
        }

        function getMention() {
            const sel = root.getSelection();
            if (!sel || !sel.rangeCount || !sel.isCollapsed) return null;
            const range = sel.getRangeAt(0);
            let textNode = range.startContainer;
            const offset = range.startOffset;
            if (textNode.nodeType !== Node.TEXT_NODE || !node.contains(textNode)) return null;
            if (textNode.parentElement?.closest('[data-note-token]')) return null;
            const before = (textNode.nodeValue || '').slice(0, offset);
            const m = before.match(/(?:^|\s)@([^@\s]{0,60})$/);
            return m ? {node: textNode, start: offset - m[1].length - 1,
                end: offset, query: m[1]} : null;
        }

        function insertMention(item, mention) {
            if (!mention || !mention.node?.isConnected || !node.contains(mention.node) ||
                !Object.hasOwn(supported,item.type) || !uuidPattern.test(String(item.id || ''))) return false;
            const n = mention.node;
            const data = n.nodeValue || '';
            if (mention.start < 0 || mention.end > data.length || data.slice(mention.start,mention.start+1) !== '@') return false;
            const title = cleanLabel(item.title);
            const link = makeLink(item.type,title,item.id,rawToken(item.type,title,item.id));
            const suffix = document.createTextNode(' ' + data.slice(mention.end));
            n.nodeValue = data.slice(0,mention.start);
            n.after(link,suffix);
            const sel = root.getSelection();
            const range = document.createRange();
            range.setStart(suffix,1);range.collapse(true);
            sel.removeAllRanges();sel.addRange(range);node.focus();
            onChange?.();
            return true;
        }

        function insertPlainText(value) {
            const sel = root.getSelection();
            if (!sel?.rangeCount) return;
            const range=sel.getRangeAt(0);
            if (!node.contains(range.commonAncestorContainer) && range.commonAncestorContainer!==node) return;
            range.deleteContents();
            const t=document.createTextNode(value);
            range.insertNode(t);
            range.setStart(t,t.length);range.collapse(true);
            sel.removeAllRanges();sel.addRange(range);
            onChange?.();
        }
        node.addEventListener('beforeinput', e => {
            if (e.inputType === 'insertParagraph' || e.inputType === 'insertLineBreak') {
                e.preventDefault(); insertPlainText('\n');
            } else if (e.inputType === 'insertFromPaste' || e.inputType === 'insertFromDrop') {
                e.preventDefault();
            }
        });
        node.addEventListener('paste', e => {
            e.preventDefault();insertPlainText(e.clipboardData?.getData('text/plain') || '');
        });
        node.addEventListener('drop', e => e.preventDefault());
        // Contenteditable anchors aren't consistently activated by a plain click
        // across Chrome/Firefox. Open only our own generated, UUID-scoped links.
        node.addEventListener('click', e => {
            const link = e.target instanceof Element ? e.target.closest('a[data-note-token]') : null;
            if (!link || !node.contains(link)) return;
            e.preventDefault();
            root.open(link.href, '_blank', 'noopener,noreferrer');
        });
        node.addEventListener('input', () => onChange?.());
        return {setValue,getValue,getMention,insertMention,focus:()=>node.focus()};
    }
    root.CrmNotesRichEditor = {editor, tokenRegex: TOKEN,
        excerpt: body => String(body || '').replace(TOKEN, (_raw,_d,title) => '@' + title)};
})(window);
