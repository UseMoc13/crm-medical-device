/* A7.4.11.2: progressive enhancement; forms continue to work when JS is disabled. */
(() => {
  'use strict';
  const root = document.getElementById('wsnTopbar');
  if (!root || !root.dataset.pollUrl || !root.dataset.openBase || !root.dataset.csrfToken) return;
  const badge = document.getElementById('wsnBadge');
  const summary = root.querySelector('summary');
  const recent = document.getElementById('wsnRecent');
  const unreadLink = document.getElementById('wsnUnreadLink');
  if (!badge || !summary || !recent || !unreadLink) return;

  let busy = false;
  const textNode = (tag, className, value) => {
    const node = document.createElement(tag);
    if (className) node.className = className;
    node.textContent = String(value ?? '');
    return node;
  };

  const makeNotice = (item) => {
    if (!item || typeof item.id !== 'string' || !/^[0-9a-f-]{36}$/i.test(item.id)) return null;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = `${root.dataset.openBase}/${encodeURIComponent(item.id)}/open`;
    form.className = 'wsn-recent-item' + (item.is_unread ? ' is-unread' : '');
    const token = document.createElement('input');
    token.type = 'hidden';
    token.name = '_token';
    token.value = root.dataset.csrfToken;
    const button = document.createElement('button');
    button.type = 'submit';
    button.title = 'Open related item';
    button.append(
      textNode('span', 'wsn-recent-title', item.title),
      textNode('span', 'wsn-recent-sub', item.message),
      textNode('small', '', item.created_label),
    );
    form.append(token, button);
    return form;
  };

  const update = (payload) => {
    if (!payload || !Array.isArray(payload.notifications) ||
        !Number.isFinite(Number(payload.unread_count))) return;
    const count = Math.max(0, Math.floor(Number(payload.unread_count)));
    badge.hidden = count === 0;
    badge.textContent = count > 99 ? '99+' : String(count);
    summary.setAttribute('aria-label', `Notifications, ${count} unread`);
    unreadLink.textContent = `${count} unread notifications`;
    const list = payload.notifications.map(makeNotice).filter(Boolean);
    recent.replaceChildren(...(list.length ? list : [textNode('p', 'wsn-empty', 'No notifications yet.')]));
  };

  const refresh = async () => {
    if (busy || document.hidden) return;
    busy = true;
    try {
      const response = await fetch(root.dataset.pollUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        cache: 'no-store',
      });
      if (!response.ok) return;
      update(await response.json());
    } catch (_) { /* Offline and expired sessions should not interrupt CRM work. */ }
    finally { busy = false; }
  };
  // Initial state is rendered server-side to avoid a flash of missing data.
  root.addEventListener('toggle', () => { if (root.open) void refresh(); });
  document.addEventListener('visibilitychange', () => { if (!document.hidden) void refresh(); });
  window.setInterval(() => { void refresh(); }, 30000);
})();
