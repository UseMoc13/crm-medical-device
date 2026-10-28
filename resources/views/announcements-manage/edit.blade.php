@extends('layouts.app')

@section('title', 'Edit Announcement')

@section('content')

@php
    $hasOldInput = session()->hasOldInput();

    $dateTimeValue = function (string $field) use ($announcement) {
        $value = old($field, $announcement->{$field});

        return $value
            ? \Illuminate\Support\Carbon::parse($value)->format('Y-m-d\TH:i')
            : '';
    };

    $fileToArray = function ($item, string $idKey) {
        return [
            'id' => (string) $item->{$idKey},
            'name' => $item->original_name,
            'url' => '/storage/' . ltrim($item->file_path, '/'),
            'type' => $item->mime_type,
            'size' => (int) $item->file_size,
            'media_type' => $item->media_type,
        ];
    };

    $existingThumbnail = $announcement->thumbnail_path
        ? [
            'url' => '/storage/' . ltrim($announcement->thumbnail_path, '/'),
            'name' => basename($announcement->thumbnail_path),
        ]
        : null;

    $existingAttachments = $announcement->attachments
        ->map(fn ($item) => $fileToArray($item, 'attachment_id'))
        ->values();

    $existingMedia = $announcement->media
        ->map(fn ($item) => $fileToArray($item, 'media_id'))
        ->values();
@endphp

<div class="page-head">
    <div>
        <h1>Edit Announcement</h1>
        <p>Update, preview, and reconfigure this company announcement.</p>
    </div>
    <div class="actions">
        <a href="{{ route('announcement-management.index') }}" class="btn">← Back</a>
        <button type="button" class="btn primary" id="openPreviewTop">Preview Announcement</button>
    </div>
</div>

@if(session('success'))
<div class="alert success">{{ session('success') }}</div>
@endif

@if($errors->any())
<div class="alert error">
    <strong>Please check the following errors:</strong>
    <ul>
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="announcement-create-layout">
    <form method="POST" action="{{ route('announcement-management.update', $announcement) }}" enctype="multipart/form-data" id="announcementEditForm">
        @csrf
        @method('PUT')

        <div class="card announcement-form-card">
            <div class="card-head">
                <div>
                    <h3>Announcement Information</h3>
                    <p>Review and update the information and content that readers will see.</p>
                </div>
                <span class="required-note"><span class="required">*</span> Required fields</span>
            </div>

            <div class="card-body">
                <section class="form-section">
                    <div class="section-heading">
                        <span class="section-number">01</span>
                        <div>
                            <h4>Basic Information</h4>
                            <p>Give your announcement a clear title and relevant tags.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="title">Announcement Title <span class="required">*</span></label>
                        <input type="text" name="title" id="title" maxlength="150" value="{{ old('title', $announcement->title) }}" placeholder="e.g. Scheduled System Maintenance" required>
                        <div class="field-footer">
                            <span class="form-hint">Use a concise title that communicates the main message.</span>
                            <span class="character-count" id="titleCount">0 / 150</span>
                        </div>
                        @error('title')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="tagSearch">Announcement Tags</label>
                        <input type="hidden" name="category" id="category" value="{{ old('category', $announcement->category) }}">
                        <div class="tag-input-shell" id="tagInputShell">
                            <div class="tag-chip-list" id="selectedTags"></div>
                            <input type="text" id="tagSearch" autocomplete="off" placeholder="Search or create a tag...">
                            <button type="button" class="tag-add-button" id="addTagButton">Add</button>
                        </div>
                        <span class="form-hint">Select an existing tag or type a new tag and press Enter. Multiple tags are supported.</span>

                        <div class="tag-recommendations">
                            <div class="tag-recommendation-heading">
                                <strong>Previously Used Tags</strong>
                                <span>Recommendations</span>
                            </div>
                            <div class="tag-recommendation-list" id="tagRecommendations"></div>
                            <p class="form-hint" id="tagRecommendationNote">Usage counts are shown when supplied by the controller.</p>
                        </div>

                        @error('category')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="summary">Short Summary</label>
                        <textarea name="summary" id="summary" rows="3" maxlength="5000" placeholder="Write a short introduction that explains why this announcement matters...">{{ old('summary', $announcement->summary) }}</textarea>
                        <div class="field-footer">
                            <span class="form-hint">A short introduction displayed on announcement cards and above the article.</span>
                            <span class="character-count" id="summaryCount">0 characters</span>
                        </div>
                        @error('summary')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <span class="section-number">02</span>
                        <div>
                            <h4>Announcement Thumbnail</h4>
                            <p>Keep, replace, or remove the cover image and adjust its appearance.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="thumbnail">Cover Image</label>
                        <input type="file" name="thumbnail" id="thumbnail" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" hidden>
                        <input type="hidden" name="remove_thumbnail" id="removeThumbnailField" value="0">

                        <div class="thumbnail-upload-box" id="thumbnailUploadBox">
                            <div class="thumbnail-placeholder" id="thumbnailPlaceholder">
                                <div class="upload-icon">↑</div>
                                <strong>Upload announcement thumbnail</strong>
                                <span>JPG, PNG, or WebP · Maximum 4 MB</span>
                                <button type="button" class="btn" id="chooseThumbnail">Choose Image</button>
                                <div class="thumbnail-removed-notice" id="thumbnailRemovedNotice" hidden>
                                    <span>The current thumbnail will be removed when you save.</span>
                                    <button type="button" class="btn" id="undoRemoveThumbnail">Undo</button>
                                </div>
                            </div>

                            <div class="thumbnail-workspace" id="thumbnailWorkspace" hidden>
                                <div class="thumbnail-stage mode-cover" id="thumbnailStage">
                                    <img src="" alt="" class="thumbnail-blur-layer" id="thumbnailBlurLayer" hidden>
                                    <div class="thumbnail-solid-layer" id="thumbnailSolidLayer" hidden></div>
                                    <img src="" alt="Thumbnail preview" class="thumbnail-main-image position-center" id="thumbnailPreview">
                                </div>

                                <div class="thumbnail-file-bar">
                                    <div class="thumbnail-file-info">
                                        <strong id="thumbnailFileName">Selected image</strong>
                                        <span id="thumbnailFileSize"></span>
                                    </div>
                                    <div class="thumbnail-actions">
                                        <button type="button" class="btn" id="replaceThumbnail">Replace</button>
                                        <button type="button" class="btn danger-light" id="removeThumbnail">Remove</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="thumbnail-settings" id="thumbnailSettings" hidden>
                            <div class="thumbnail-settings-heading">
                                <strong>Thumbnail Appearance</strong>
                                <span>Choose how the cover image is displayed. These settings are saved with the announcement.</span>
                            </div>

                            <div class="thumbnail-settings-grid">
                                <div class="form-group">
                                    <label for="thumbnailFit">Image Mode</label>
                                    <select id="thumbnailFit" name="thumbnail_fit">
                                        <option value="cover" @selected(old('thumbnail_fit', $announcement->thumbnail_fit ?? 'cover') === 'cover')>Cover — Fill the entire area</option>
                                        <option value="fit" @selected(old('thumbnail_fit', $announcement->thumbnail_fit ?? 'cover') === 'fit')>Fit — Show the full image</option>
                                        <option value="blur" @selected(old('thumbnail_fit', $announcement->thumbnail_fit ?? 'cover') === 'blur')>Blur — Use a blurred image background</option>
                                    </select>
                                    <span class="form-hint">Cover fills the frame, Fit preserves the entire image, and Blur fills empty areas with a blurred version of the image.</span>
                                </div>

                                <div class="form-group" id="thumbnailPositionGroup">
                                    <label for="thumbnailPosition">Image Position</label>
                                    <select id="thumbnailPosition" name="thumbnail_position">
                                        <option value="center" @selected(old('thumbnail_position', $announcement->thumbnail_position ?? 'center') === 'center')>Center</option>
                                        <option value="top" @selected(old('thumbnail_position', $announcement->thumbnail_position ?? 'center') === 'top')>Top</option>
                                        <option value="bottom" @selected(old('thumbnail_position', $announcement->thumbnail_position ?? 'center') === 'bottom')>Bottom</option>
                                        <option value="left" @selected(old('thumbnail_position', $announcement->thumbnail_position ?? 'center') === 'left')>Left</option>
                                        <option value="right" @selected(old('thumbnail_position', $announcement->thumbnail_position ?? 'center') === 'right')>Right</option>
                                    </select>
                                </div>

                                <div class="form-group" id="thumbnailBackgroundStyleGroup" hidden>
                                    <label for="thumbnailBackgroundStyle">Background Style</label>
                                    <select id="thumbnailBackgroundStyle" name="thumbnail_background_style">
                                        <option value="solid" @selected(old('thumbnail_background_style', $announcement->thumbnail_background_style ?? 'solid') === 'solid')>Solid Color</option>
                                        <option value="gradient" @selected(old('thumbnail_background_style', $announcement->thumbnail_background_style ?? 'solid') === 'gradient')>Gradient</option>
                                        <option value="transparent" @selected(old('thumbnail_background_style', $announcement->thumbnail_background_style ?? 'solid') === 'transparent')>Transparent</option>
                                    </select>
                                </div>

                                <div class="form-group" id="thumbnailSolidColorGroup" hidden>
                                    <label for="thumbnailBackground">Solid Background Color</label>
                                    <div class="color-control">
                                        <input type="color" id="thumbnailBackground" name="thumbnail_background" value="{{ old('thumbnail_background', $announcement->thumbnail_background ?? '#17284f') }}">
                                        <span id="thumbnailBackgroundValue">#17284F</span>
                                    </div>
                                </div>

                                <div class="form-group" id="thumbnailGradientStartGroup" hidden>
                                    <label for="thumbnailGradientStart">Gradient Start Color</label>
                                    <div class="color-control">
                                        <input type="color" id="thumbnailGradientStart" name="thumbnail_gradient_start" value="{{ old('thumbnail_gradient_start', $announcement->thumbnail_gradient_start ?? '#17284f') }}">
                                        <span id="thumbnailGradientStartValue">#17284F</span>
                                    </div>
                                </div>

                                <div class="form-group" id="thumbnailGradientEndGroup" hidden>
                                    <label for="thumbnailGradientEnd">Gradient End Color</label>
                                    <div class="color-control">
                                        <input type="color" id="thumbnailGradientEnd" name="thumbnail_gradient_end" value="{{ old('thumbnail_gradient_end', $announcement->thumbnail_gradient_end ?? '#2ba7a0') }}">
                                        <span id="thumbnailGradientEndValue">#2BA7A0</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @error('thumbnail')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <span class="section-number">03</span>
                        <div>
                            <h4>Announcement Content</h4>
                            <p>Write and format the message with headings, links, images, and styled tables.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="contentEditor">Message Content <span class="required">*</span></label>
                        <textarea id="initialContent" hidden>{{ old('content', $announcement->content) }}</textarea>

                        <div class="rich-editor">
                            <div class="editor-toolbar" role="toolbar" aria-label="Announcement formatting tools">
                                <div class="toolbar-group">
                                    <select id="editorBlock" aria-label="Text style" title="Text style">
                                        <option value="p">Paragraph</option>
                                        <option value="h2">Heading 1</option>
                                        <option value="h3">Heading 2</option>
                                        <option value="h4">Heading 3</option>
                                        <option value="blockquote">Quote</option>
                                    </select>
                                </div>
                                <span class="toolbar-divider"></span>
                                <div class="toolbar-group">
                                    <button type="button" data-command="bold" title="Bold"><strong>B</strong></button>
                                    <button type="button" data-command="italic" title="Italic"><em>I</em></button>
                                    <button type="button" data-command="underline" title="Underline"><u>U</u></button>
                                    <button type="button" data-command="strikeThrough" title="Strikethrough"><s>S</s></button>
                                </div>
                                <span class="toolbar-divider"></span>
                                <div class="toolbar-group">
                                    <button type="button" data-command="insertUnorderedList" title="Bullet list">• List</button>
                                    <button type="button" data-command="insertOrderedList" title="Numbered list">1. List</button>
                                    <button type="button" id="alignLeft" title="Align left">≡</button>
                                    <button type="button" id="alignCenter" title="Align center">☰</button>
                                    <button type="button" id="alignRight" title="Align right">≡</button>
                                </div>
                                <span class="toolbar-divider"></span>
                                <div class="toolbar-group">
                                    <button type="button" id="insertLinkButton" title="Insert link">↗ Link</button>
                                    <button type="button" id="insertImageButton" title="Insert image">▧ Image</button>
                                    <button type="button" id="insertTableButton" title="Insert table">▦ Table</button>
                                </div>
                                <span class="toolbar-divider"></span>
                                <div class="toolbar-group">
                                    <button type="button" id="undoEditor" title="Undo">↶</button>
                                    <button type="button" id="redoEditor" title="Redo">↷</button>
                                    <button type="button" id="clearEditor" class="toolbar-clear" title="Clear all content">Clear All</button>
                                </div>
                            </div>

                            <div class="editor-table-tools" id="editorTableTools" hidden>
                                <span class="table-tools-label">Table tools</span>
                                <button type="button" id="addTableRow">+ Row</button>
                                <button type="button" id="removeTableRow">− Row</button>
                                <button type="button" id="addTableColumn">+ Column</button>
                                <button type="button" id="removeTableColumn">− Column</button>
                                <button type="button" id="deleteTable" class="table-delete">Delete Table</button>
                                <span class="table-tools-hint">Click a table cell before using these controls.</span>

                                <div class="table-style-controls">
                                    <label>
                                        Header Background
                                        <input type="color" id="tableHeaderBackground" value="#f5f7fb">
                                    </label>
                                    <label>
                                        Header Text
                                        <input type="color" id="tableHeaderColor" value="#17284f">
                                    </label>
                                    <label>
                                        Cell Background
                                        <input type="color" id="tableCellBackground" value="#ffffff">
                                    </label>
                                    <label>
                                        Cell Text
                                        <input type="color" id="tableCellColor" value="#34415c">
                                    </label>
                                </div>
                            </div>

                            <div id="contentEditor" class="editor-content" contenteditable="true" role="textbox" aria-multiline="true" aria-label="Announcement message content" data-placeholder="Write your announcement here. You can format text, insert images, add tables, and edit the content just like an email."></div>

                            <input type="file" id="editorImageInput" accept="image/jpeg,image/png,image/webp,image/gif" hidden>
                        </div>

                        <textarea name="content" id="content" hidden required>{{ old('content', $announcement->content) }}</textarea>

                        <div class="field-footer">
                            <span class="form-hint">Select a table cell to reveal table editing and color controls.</span>
                            <span class="character-count" id="contentCount">0 characters</span>
                        </div>

                        @error('content')
                        <span class="form-error">{{ $message }}</span>
                        @enderror

                        <div class="editor-notice">
                            <span class="notice-icon">i</span>
                            <p>Inserted editor images are embedded in the content for this browser preview. Persistent image storage and server-side HTML sanitization must also be handled by the controller.</p>
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <span class="section-number">04</span>
                        <div>
                            <h4>Attachments</h4>
                            <p>Review current files, remove outdated ones, or add new files.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="existing-files-block" id="existingAttachmentsBlock" hidden>
                            <div class="existing-files-heading">
                                <strong>Current Attachments</strong>
                                <span id="existingAttachmentsCount"></span>
                            </div>
                            <div class="selected-files-list" id="existingAttachmentsList"></div>
                            <div id="removeAttachmentInputs"></div>
                        </div>

                        <div class="existing-files-block" id="existingMediaBlock" hidden>
                            <div class="existing-files-heading">
                                <strong>Current Media</strong>
                                <span id="existingMediaCount"></span>
                            </div>
                            <div class="selected-files-list" id="existingMediaList"></div>
                            <div id="removeMediaInputs"></div>
                        </div>

                        <label for="attachmentFiles">Add New Attachments</label>
                        <input type="file" name="attachment_files[]" id="attachmentFiles" multiple hidden>

                        <div class="upload-dropzone attachment-dropzone" id="attachmentDropzone">
                            <div class="upload-icon">↥</div>
                            <strong>Drag and drop files here</strong>
                            <span>Or choose multiple files from your computer</span>
                            <button type="button" class="btn" id="chooseAttachments">Choose Files</button>
                            <span class="upload-limit">Maximum combined selection: 100 MB</span>
                        </div>

                        <div class="attachment-summary" id="attachmentSummary" hidden>
                            <span id="attachmentCount">0 files</span>
                            <span id="attachmentTotalSize">0 MB / 100 MB</span>
                        </div>

                        <div class="selected-files-list" id="attachmentFilesPreview"></div>
                        <div class="upload-warning" id="attachmentWarning" hidden></div>

                        <span class="form-hint">Image files display thumbnails, PDFs display a preview, and other files display their file type and size.</span>

                        @error('attachment_files')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                        @error('attachment_files.*')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>
                </section>

                <section class="form-section">
                    <div class="section-heading">
                        <span class="section-number">05</span>
                        <div>
                            <h4>Publication Settings</h4>
                            <p>Change the status, schedule, expiration, or pin settings of this announcement.</p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Publication Status <span class="required">*</span></label>
                        <select name="status" id="status" required>
                            <option value="draft" @selected(old('status', $announcement->status) === 'draft')>Draft</option>
                            <option value="scheduled" @selected(old('status', $announcement->status) === 'scheduled')>Scheduled</option>
                            <option value="published" @selected(old('status', $announcement->status) === 'published')>Published</option>
                            <option value="archived" @selected(old('status', $announcement->status) === 'archived')>Archived</option>
                        </select>
                        <span class="form-hint" id="statusDescription">Save the announcement privately and continue editing it later.</span>
                        @error('status')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="publication-settings-panel" id="scheduledSettings" hidden>
                        <div class="settings-panel-heading">
                            <span class="settings-icon">◷</span>
                            <div>
                                <strong>Schedule Publication</strong>
                                <p>Choose the future date and time when the announcement should go live.</p>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="scheduled_at">Scheduled Date and Time <span class="required">*</span></label>
                            <input type="datetime-local" name="scheduled_at" id="scheduled_at" value="{{ $dateTimeValue('scheduled_at') }}">
                            <span class="form-hint">The announcement is published automatically at this time. It must be in the future.</span>
                            @error('scheduled_at')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="publication-settings-panel" id="publishedSettings" hidden>
                        <div class="settings-panel-heading">
                            <span class="settings-icon">✓</span>
                            <div>
                                <strong>Publish Announcement</strong>
                                <p>The announcement becomes visible to readers as soon as you save it.</p>
                            </div>
                        </div>
                        <div class="publication-instruction">
                            <strong>Published immediately</strong>
                            @if($announcement->published_at)
                            <p>This announcement was published on {{ $announcement->published_at->format('d M Y, H:i') }}. Saving keeps that publication time. If it should go live later, choose <b>Scheduled</b> instead.</p>
                            @else
                            <p>The publication time is set automatically when you save. If it should go live later, choose <b>Scheduled</b> instead.</p>
                            @endif
                        </div>
                    </div>

                    <div class="publication-settings-panel" id="archivedSettings" hidden>
                        <div class="settings-panel-heading">
                            <span class="settings-icon">▣</span>
                            <div>
                                <strong>Archive Announcement</strong>
                                <p>This announcement is intended to remain outside the public announcement list.</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group" id="expirationGroup">
                        <label for="expires_at">Expiration Date and Time <span class="optional-label">Optional</span></label>
                        <input type="datetime-local" name="expires_at" id="expires_at" value="{{ $dateTimeValue('expires_at') }}">
                        <span class="form-hint">The announcement is hidden from readers automatically after this time. It must be later than the publication time.</span>
                        @error('expires_at')
                        <span class="form-error">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="pin-setting">
                        <label class="pin-checkbox" for="is_pinned">
                            <input type="checkbox" name="is_pinned" id="is_pinned" value="1" @checked($hasOldInput ? old('is_pinned') : $announcement->is_pinned)>
                            <span>
                                <strong>Pin this announcement</strong>
                                <small>Prioritize this announcement above other items in supported announcement lists.</small>
                            </span>
                        </label>
                        <div class="form-group pin-order-group" id="pinOrderGroup" hidden>
                            <label for="pin_order">Pin Priority</label>
                            <input type="number" name="pin_order" id="pin_order" min="1" value="{{ old('pin_order', $announcement->pin_order ?? 1) }}">
                            <span class="form-hint">Smaller numbers have higher priority.</span>
                            @error('pin_order')
                            <span class="form-error">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </section>

                <div class="form-actions">
                    <a href="{{ route('announcement-management.index') }}" class="btn">Cancel</a>
                    <button type="button" class="btn" id="openPreviewBottom">Preview as Reader</button>
                    <button type="submit" class="btn primary" id="submitAnnouncementButton">Save Changes</button>
                </div>
            </div>
        </div>
    </form>

    <aside class="announcement-side-panel">
        <div class="side-panel-card">
            <div class="side-panel-title">
                <span class="side-panel-icon">◈</span>
                <div>
                    <h3>Live Preview</h3>
                    <p>Updates as you edit the announcement.</p>
                </div>
            </div>

            <div class="mini-announcement-card">
                <div class="mini-thumbnail mode-cover" id="miniThumbnailWrap">
                    <img src="" alt="" class="mini-thumbnail-blur" id="miniThumbnailBlur" hidden>
                    <div class="mini-thumbnail-solid" id="miniThumbnailSolid" hidden></div>
                    <img src="" alt="Announcement thumbnail" id="miniThumbnail" hidden>
                    <div class="mini-thumbnail-placeholder" id="miniThumbnailPlaceholder">
                        <span>◈</span>
                        <small>Announcement image</small>
                    </div>
                    <span class="mini-pin" id="miniPin" hidden>PINNED</span>
                </div>
                <div class="mini-card-body">
                    <div class="mini-category" id="miniCategory">GENERAL ANNOUNCEMENT</div>
                    <h3 id="miniTitle">Your announcement title</h3>
                    <p id="miniSummary">Your short summary will appear here to help readers understand the announcement.</p>
                    <div class="mini-meta">
                        <span id="miniDate">Publication date not set</span>
                        <span class="mini-status" id="miniStatus">DRAFT</span>
                    </div>
                </div>
            </div>

            <button type="button" class="btn preview-full-button" id="openPreviewSide">Open Reader Preview ↗</button>
        </div>

        <div class="side-panel-card checklist-card">
            <h3>Before Publishing</h3>
            <p>Use this checklist to review your announcement.</p>
            <div class="checklist-item" id="checkTitle"><span class="check-icon">○</span><span>Announcement title added</span></div>
            <div class="checklist-item" id="checkSummary"><span class="check-icon">○</span><span>Summary prepared</span></div>
            <div class="checklist-item" id="checkContent"><span class="check-icon">○</span><span>Content written</span></div>
            <div class="checklist-item" id="checkThumbnail"><span class="check-icon">○</span><span>Thumbnail selected</span></div>
            <div class="checklist-item" id="checkPublication"><span class="check-icon">○</span><span>Publication settings reviewed</span></div>
        </div>

        <div class="side-panel-card visibility-card">
            <div class="visibility-heading">
                <span class="visibility-dot" id="visibilityDot"></span>
                <strong id="visibilityTitle">Private draft</strong>
            </div>
            <p id="visibilityDescription">Only authorized users should access drafts through the management area.</p>
        </div>

        <div class="side-panel-card details-card">
            <h3>Announcement Details</h3>
            <dl class="details-list">
                <div>
                    <dt>Created</dt>
                    <dd>{{ optional($announcement->created_at)->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Created by</dt>
                    <dd>{{ data_get($announcement, 'creator.name', '—') }}</dd>
                </div>
                <div>
                    <dt>Last updated</dt>
                    <dd>{{ optional($announcement->updated_at)->format('d M Y, H:i') ?? '—' }}</dd>
                </div>
                <div>
                    <dt>Last updated by</dt>
                    <dd>{{ data_get($announcement, 'updater.name', '—') }}</dd>
                </div>
                <div>
                    <dt>Slug</dt>
                    <dd class="details-slug">{{ $announcement->slug }}</dd>
                </div>
            </dl>
        </div>
    </aside>
</div>

<div class="reader-preview-modal" id="readerPreviewModal" aria-hidden="true">
    <div class="reader-preview-backdrop" data-close-preview></div>
    <div class="reader-preview-dialog" role="dialog" aria-modal="true" aria-labelledby="readerPreviewTitle">
        <div class="reader-preview-toolbar">
            <div class="reader-brand">
                <img src="{{ asset('assets/logo.png') }}" alt="PT Radja Usada Mandiri" id="readerLogo" onerror="this.hidden=true; document.getElementById('readerLogoFallback').hidden=false;">
                <div class="reader-logo-fallback" id="readerLogoFallback" hidden>R</div>
                <div>
                    <span class="reader-preview-eyebrow">PT RADJA USADA MANDIRI</span>
                    <h3 id="readerPreviewTitle">Announcement Preview</h3>
                </div>
            </div>
            <button type="button" class="reader-close-button" id="closePreview" aria-label="Close preview">×</button>
        </div>

        <div class="reader-preview-scroll">
            <article class="reader-article">
                <div class="reader-cover mode-cover" id="readerCoverWrap" hidden>
                    <img src="" alt="" class="reader-cover-blur" id="readerCoverBlur" hidden>
                    <div class="reader-cover-solid" id="readerCoverSolid" hidden></div>
                    <img src="" alt="Announcement cover" class="reader-cover-main" id="readerCover">
                </div>
                <div class="reader-article-content">
                    <div class="reader-category" id="readerCategory">General Announcement</div>
                    <h1 id="readerTitle">Your announcement title</h1>
                    <div class="reader-byline">
                        <div class="reader-author-avatar">R</div>
                        <div>
                            <strong>PT Radja Usada Mandiri</strong>
                            <span id="readerPublicationDate">Publication date not set</span>
                        </div>
                    </div>
                    <div class="reader-pinned-label" id="readerPinned" hidden>★ Pinned announcement</div>
                    <p class="reader-summary" id="readerSummary">Your announcement summary will appear here.</p>
                    <div class="reader-body" id="readerContent"></div>
                    <section class="reader-attachments-section" id="readerAttachmentsSection" hidden>
                        <h2>Attachments</h2>
                        <div id="readerAttachmentsList"></div>
                    </section>
                </div>
            </article>
        </div>
        <div class="reader-preview-footer">
            <span>This is a browser preview. Actual visibility depends on publication status, server-side storage, and access rules.</span>
            <button type="button" class="btn primary" data-close-preview>Back to Editor</button>
        </div>
    </div>
</div>

<style>
    .announcement-create-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 310px;
        align-items: start;
        gap: 22px
    }

    .announcement-create-layout>form,
    .announcement-form-card,
    .announcement-side-panel {
        min-width: 0
    }

    .announcement-form-card {
        overflow: visible
    }

    .announcement-form-card .card-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 16px
    }

    .announcement-form-card .card-head h3 {
        margin: 0 0 5px;
        color: #17284f;
        font-size: 16px
    }

    .announcement-form-card .card-head p {
        margin: 0;
        color: #8a94a6;
        font-size: 12px;
        line-height: 1.6
    }

    .required-note,
    .optional-label {
        color: #8a94a6;
        font-size: 11px;
        font-weight: 400
    }

    .required {
        color: #d34c4c
    }

    .form-section {
        padding: 3px 0 24px;
        margin-bottom: 23px;
        border-bottom: 1px solid #edf0f5
    }

    .form-section:last-of-type {
        margin-bottom: 0
    }

    .section-heading {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 22px
    }

    .section-number {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 31px;
        height: 31px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #15966f;
        font-size: 11px;
        font-weight: 700
    }

    .section-heading h4 {
        margin: 1px 0 5px;
        color: #17284f;
        font-size: 14px;
        font-weight: 700
    }

    .section-heading p {
        margin: 0;
        color: #8a94a6;
        font-size: 11px;
        line-height: 1.6
    }

    .form-group {
        min-width: 0;
        margin-bottom: 20px
    }

    .form-group:last-child {
        margin-bottom: 0
    }

    .form-group>label {
        display: block;
        margin-bottom: 8px;
        color: #17284f;
        font-size: 12px;
        font-weight: 700
    }

    .form-group input:not([type=checkbox]):not([type=file]):not([type=color]),
    .form-group select,
    .form-group textarea {
        display: block;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #dce2eb;
        border-radius: 8px;
        background: #fff;
        color: #17284f;
        font: inherit;
        font-size: 12px;
        outline: none;
        transition: border-color .2s, box-shadow .2s
    }

    .form-group input:not([type=checkbox]):not([type=file]):not([type=color]),
    .form-group select {
        height: 42px;
        padding: 0 12px
    }

    .form-group textarea {
        min-height: 90px;
        padding: 11px 12px;
        line-height: 1.65;
        resize: vertical
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .09)
    }

    .form-group input::placeholder,
    .form-group textarea::placeholder {
        color: #a3adbc
    }

    .field-footer {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        gap: 12px;
        margin-top: 6px
    }

    .form-hint {
        display: block;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    .character-count {
        flex-shrink: 0;
        color: #98a1af;
        font-size: 10px
    }

    .form-error {
        display: block;
        margin-top: 7px;
        color: #c94a4a;
        font-size: 11px;
        line-height: 1.5
    }

    [hidden] {
        display: none !important
    }

    .upload-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        background: #eaf7f3;
        color: #15966f;
        font-size: 20px
    }

    .thumbnail-upload-box,
    .upload-dropzone {
        border: 1px dashed #cbd5e1;
        border-radius: 10px;
        background: #fafbfd;
        overflow: hidden
    }

    .thumbnail-placeholder {
        display: flex;
        min-height: 190px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 22px;
        text-align: center
    }

    .thumbnail-placeholder strong,
    .upload-dropzone strong {
        color: #34415c;
        font-size: 12px
    }

    .thumbnail-placeholder>span,
    .upload-dropzone>span {
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    .thumbnail-workspace {
        width: 100%
    }

    .thumbnail-stage,
    .mini-thumbnail,
    .reader-cover {
        position: relative;
        isolation: isolate;
        overflow: hidden;
        background: #17284f
    }

    .thumbnail-stage {
        height: 250px;
        width: 100%
    }

    .thumbnail-blur-layer,
    .thumbnail-solid-layer,
    .thumbnail-main-image,
    .mini-thumbnail-blur,
    .mini-thumbnail-solid,
    #miniThumbnail,
    .reader-cover-blur,
    .reader-cover-solid,
    .reader-cover-main {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%
    }

    .thumbnail-blur-layer,
    .mini-thumbnail-blur,
    .reader-cover-blur {
        z-index: 0;
        object-fit: cover;
        filter: blur(22px) brightness(.78);
        transform: scale(1.13)
    }

    .thumbnail-solid-layer,
    .mini-thumbnail-solid,
    .reader-cover-solid {
        z-index: 0;
        background: #17284f
    }

    .thumbnail-main-image,
    #miniThumbnail,
    .reader-cover-main {
        z-index: 2;
        display: block;
        object-fit: cover
    }

    .mode-cover .thumbnail-main-image,
    .mode-cover #miniThumbnail,
    .mode-cover .reader-cover-main {
        object-fit: cover
    }

    .mode-fit .thumbnail-main-image,
    .mode-fit #miniThumbnail,
    .mode-fit .reader-cover-main {
        object-fit: contain
    }

    .mode-blur .thumbnail-main-image,
    .mode-blur #miniThumbnail,
    .mode-blur .reader-cover-main {
        object-fit: contain
    }

    .position-center {
        object-position: center
    }

    .position-top {
        object-position: center top
    }

    .position-bottom {
        object-position: center bottom
    }

    .position-left {
        object-position: left center
    }

    .position-right {
        object-position: right center
    }

    .thumbnail-file-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: #f4f7fb
    }

    .thumbnail-file-info {
        min-width: 0
    }

    .thumbnail-file-info strong,
    .thumbnail-file-info span {
        display: block;
        overflow-wrap: anywhere
    }

    .thumbnail-file-info strong {
        color: #34415c;
        font-size: 11px
    }

    .thumbnail-file-info span {
        margin-top: 4px;
        color: #8a94a6;
        font-size: 10px
    }

    .thumbnail-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 7px
    }

    .danger-light {
        color: #b34b4b !important;
        border-color: #f0d9d9 !important;
        background: #fff7f7 !important
    }

    .thumbnail-settings {
        margin-top: 13px;
        padding: 15px;
        border: 1px solid #e3e9f0;
        border-radius: 9px;
        background: #fafbfd
    }

    .thumbnail-settings-heading {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-bottom: 15px
    }

    .thumbnail-settings-heading strong {
        color: #17284f;
        font-size: 12px
    }

    .thumbnail-settings-heading span {
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    .thumbnail-settings-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px
    }

    .thumbnail-settings-grid .form-group {
        margin-bottom: 10px
    }

    .color-control {
        display: flex;
        align-items: center;
        gap: 10px;
        height: 42px;
        padding: 5px 10px;
        border: 1px solid #dce2eb;
        border-radius: 8px;
        background: #fff
    }

    .color-control input[type=color] {
        width: 30px;
        height: 30px;
        padding: 0;
        border: 0;
        background: transparent;
        cursor: pointer
    }

    .color-control span {
        color: #34415c;
        font-size: 11px
    }

    .tag-input-shell {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 7px;
        min-height: 44px;
        padding: 7px 9px;
        border: 1px solid #dce2eb;
        border-radius: 8px;
        background: #fff
    }

    .tag-input-shell:focus-within {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .09)
    }

    .tag-chip-list {
        display: contents
    }

    .tag-chip {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        max-width: 100%;
        padding: 6px 8px;
        border-radius: 6px;
        background: #eaf7f3;
        color: #167d70;
        font-size: 10px;
        font-weight: 700
    }

    .tag-chip span {
        overflow-wrap: anywhere
    }

    .tag-chip button {
        padding: 0;
        border: 0;
        background: transparent;
        color: #167d70;
        font-size: 14px;
        cursor: pointer
    }

    #tagSearch {
        flex: 1;
        min-width: 120px;
        height: 28px;
        padding: 0;
        border: 0;
        outline: 0;
        background: transparent;
        color: #17284f;
        font: inherit;
        font-size: 12px
    }

    #tagSearch:focus {
        border: 0;
        box-shadow: none
    }

    .tag-add-button {
        padding: 6px 10px;
        border: 1px solid #dce8e4;
        border-radius: 6px;
        background: #f3faf7;
        color: #167d70;
        font-size: 10px;
        font-weight: 700;
        cursor: pointer
    }

    .tag-recommendations {
        margin-top: 12px;
        padding: 12px;
        border: 1px solid #e6ebf1;
        border-radius: 8px;
        background: #fafbfd
    }

    .tag-recommendation-heading {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 10px
    }

    .tag-recommendation-heading strong {
        color: #34415c;
        font-size: 10px
    }

    .tag-recommendation-heading span {
        color: #8a94a6;
        font-size: 9px
    }

    .tag-recommendation-list {
        display: flex;
        flex-wrap: wrap;
        gap: 7px
    }

    .tag-suggestion {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 9px;
        border: 1px solid #dce5e9;
        border-radius: 6px;
        background: #fff;
        color: #526176;
        font-size: 10px;
        cursor: pointer
    }

    .tag-suggestion:hover {
        border-color: #b9dfd2;
        background: #f1faf6;
        color: #167d70
    }

    .tag-suggestion-count {
        color: #8a94a6;
        font-size: 9px
    }

    .tag-empty-message {
        color: #8a94a6;
        font-size: 10px
    }

    .rich-editor {
        overflow: hidden;
        border: 1px solid #dce2eb;
        border-radius: 9px;
        background: #fff
    }

    .rich-editor:focus-within {
        border-color: #2ba7a0;
        box-shadow: 0 0 0 3px rgba(43, 167, 160, .08)
    }

    .editor-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 5px;
        padding: 9px;
        border-bottom: 1px solid #e8edf3;
        background: #f8fafc
    }

    .toolbar-group {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 3px
    }

    .editor-toolbar button,
    .editor-toolbar select {
        min-width: 31px;
        height: 32px;
        padding: 0 8px;
        border: 1px solid transparent;
        border-radius: 6px;
        background: transparent;
        color: #34415c;
        font-family: inherit;
        font-size: 11px;
        cursor: pointer
    }

    .editor-toolbar select {
        border-color: #e0e5ec;
        background: #fff
    }

    .editor-toolbar button:hover,
    .editor-toolbar button.active {
        border-color: #d9e5e2;
        background: #eaf7f3;
        color: #167d70
    }

    .editor-toolbar .toolbar-clear {
        color: #b34b4b
    }

    .toolbar-divider {
        width: 1px;
        height: 23px;
        margin: 0 3px;
        background: #dfe5ed
    }

    .editor-content {
        min-height: 340px;
        padding: 18px;
        color: #34415c;
        font-family: Arial, Helvetica, sans-serif;
        font-size: 13px;
        line-height: 1.8;
        outline: none;
        overflow-wrap: anywhere
    }

    .editor-content:empty::before {
        content: attr(data-placeholder);
        color: #a3adbc;
        pointer-events: none
    }

    .editor-content h2,
    .editor-content h3,
    .editor-content h4,
    .reader-body h2,
    .reader-body h3,
    .reader-body h4 {
        color: #17284f;
        line-height: 1.4
    }

    .editor-content h2 {
        font-size: 24px
    }

    .editor-content h3 {
        font-size: 20px
    }

    .editor-content h4 {
        font-size: 16px
    }

    .editor-content img,
    .reader-body img {
        display: block;
        max-width: 100%;
        height: auto;
        margin: 14px 0;
        border-radius: 6px
    }

    .editor-content table,
    .reader-body table {
        width: 100%;
        margin: 16px 0;
        border-collapse: collapse;
        table-layout: fixed
    }

    .editor-content th,
    .editor-content td,
    .reader-body th,
    .reader-body td {
        min-width: 45px;
        padding: 8px;
        border: 1px solid #dce2eb;
        text-align: left;
        overflow-wrap: anywhere
    }

    .editor-content th,
    .reader-body th {
        background: #f5f7fb;
        color: #17284f;
        font-weight: 700
    }

    .editor-content blockquote,
    .reader-body blockquote {
        margin: 20px 0;
        padding: 12px 18px;
        border-left: 3px solid #2ba7a0;
        background: #f5f9f8;
        color: #59667b
    }

    .editor-table-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        padding: 10px 12px;
        border-bottom: 1px solid #e8edf3;
        background: #f0f7f5
    }

    .table-tools-label {
        margin-right: 4px;
        color: #17284f;
        font-size: 10px;
        font-weight: 700
    }

    .editor-table-tools>button {
        padding: 6px 9px;
        border: 1px solid #d6e5df;
        border-radius: 5px;
        background: #fff;
        color: #167d70;
        font-size: 10px;
        cursor: pointer
    }

    .editor-table-tools>button:hover {
        background: #eaf7f3
    }

    .editor-table-tools .table-delete {
        border-color: #f0d9d9;
        color: #b34b4b
    }

    .table-tools-hint {
        color: #8a94a6;
        font-size: 10px
    }

    .table-style-controls {
        display: flex;
        flex-wrap: wrap;
        gap: 9px;
        width: 100%;
        padding-top: 9px;
        border-top: 1px solid #dce8e3
    }

    .table-style-controls label {
        display: flex;
        align-items: center;
        gap: 6px;
        color: #566176;
        font-size: 10px
    }

    .table-style-controls input[type=color] {
        width: 25px;
        height: 25px;
        padding: 0;
        border: 1px solid #dce2eb;
        border-radius: 5px;
        background: #fff;
        cursor: pointer
    }

    .editor-notice {
        display: flex;
        align-items: flex-start;
        gap: 9px;
        margin-top: 12px;
        padding: 11px 12px;
        border-radius: 7px;
        background: #f3f7fb
    }

    .notice-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #e0eaf5;
        color: #42628a;
        font-size: 11px;
        font-weight: 700
    }

    .editor-notice p {
        margin: 0;
        color: #68758a;
        font-size: 10px;
        line-height: 1.6
    }

    .upload-dropzone {
        display: flex;
        min-height: 180px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 9px;
        padding: 22px;
        text-align: center;
        transition: background .2s, border-color .2s
    }

    .upload-dropzone.drag-over {
        border-color: #15966f;
        background: #effaf6
    }

    .upload-limit {
        font-weight: 700
    }

    .attachment-summary {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        margin-top: 13px;
        padding: 10px 12px;
        border: 1px solid #e5eaf0;
        border-radius: 7px;
        background: #f8fafc;
        color: #566176;
        font-size: 10px
    }

    .selected-files-list {
        display: grid;
        gap: 8px;
        margin-top: 12px
    }

    .selected-file {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 11px;
        border: 1px solid #e5eaf0;
        border-radius: 8px;
        background: #fff
    }

    .file-type-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 40px;
        height: 43px;
        border-radius: 7px;
        background: #edf2f8;
        color: #34415c;
        font-size: 9px;
        font-weight: 700;
        text-transform: uppercase
    }

    .file-preview-thumb {
        display: block;
        width: 48px;
        height: 48px;
        flex-shrink: 0;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #e5eaf0;
        background: #f5f7fb
    }

    .file-preview-pdf {
        width: 100px;
        height: 76px;
        flex-shrink: 0;
        border: 1px solid #e5eaf0;
        border-radius: 5px;
        background: #f5f7fb
    }

    .selected-file-info {
        flex: 1;
        min-width: 0
    }

    .selected-file-info strong,
    .selected-file-info span {
        display: block;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap
    }

    .selected-file-info strong {
        color: #34415c;
        font-size: 11px
    }

    .selected-file-info span {
        margin-top: 4px;
        color: #8a94a6;
        font-size: 10px
    }

    .remove-file-button {
        flex-shrink: 0;
        border: 0;
        background: transparent;
        color: #c94a4a;
        font-size: 11px;
        cursor: pointer
    }

    .upload-warning {
        margin-top: 10px;
        padding: 10px 12px;
        border: 1px solid #f1d3d3;
        border-radius: 7px;
        background: #fff7f7;
        color: #b34b4b;
        font-size: 11px;
        line-height: 1.6
    }

    .publication-settings-panel {
        margin: 13px 0 19px;
        padding: 16px;
        border: 1px solid #e1e9ef;
        border-radius: 9px;
        background: #f9fbfd
    }

    .settings-panel-heading {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 16px
    }

    .settings-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 31px;
        height: 31px;
        border-radius: 8px;
        background: #eaf7f3;
        color: #15966f;
        font-size: 15px
    }

    .settings-panel-heading strong {
        display: block;
        margin-bottom: 4px;
        color: #17284f;
        font-size: 12px
    }

    .settings-panel-heading p {
        margin: 0;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    .publication-instruction {
        margin-bottom: 16px;
        padding: 12px;
        border: 1px solid #cfe9de;
        border-radius: 7px;
        background: #f0faf5
    }

    .publication-instruction strong {
        color: #167d70;
        font-size: 11px
    }

    .publication-instruction p {
        margin: 6px 0 0;
        color: #536a63;
        font-size: 11px;
        line-height: 1.7
    }

    .pin-setting {
        padding: 15px;
        border: 1px solid #e3e9f0;
        border-radius: 9px;
        background: #fafbfd
    }

    .pin-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        cursor: pointer
    }

    .pin-checkbox input {
        width: 16px;
        height: 16px;
        margin: 2px 0 0;
        accent-color: #15966f
    }

    .pin-checkbox strong,
    .pin-checkbox small {
        display: block
    }

    .pin-checkbox strong {
        color: #17284f;
        font-size: 12px
    }

    .pin-checkbox small {
        margin-top: 5px;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    .pin-order-group {
        margin-top: 17px
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        flex-wrap: wrap;
        gap: 8px;
        padding-top: 20px
    }

    .announcement-side-panel {
        position: sticky;
        top: 86px;
        display: grid;
        gap: 15px
    }

    .side-panel-card {
        padding: 17px;
        border: 1px solid #e6eaf0;
        border-radius: 11px;
        background: #fff
    }

    .side-panel-title {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 16px
    }

    .side-panel-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        border-radius: 9px;
        background: #eaf7f3;
        color: #15966f;
        font-size: 18px
    }

    .side-panel-title h3,
    .checklist-card h3 {
        margin: 0 0 4px;
        color: #17284f;
        font-size: 12px
    }

    .side-panel-title p,
    .checklist-card>p {
        margin: 0;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.5
    }

    .mini-announcement-card {
        overflow: hidden;
        border: 1px solid #e7ebf1;
        border-radius: 9px;
        background: #fff
    }

    .mini-thumbnail {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 145px;
        background: #edf2f8
    }

    .mini-thumbnail-placeholder {
        position: relative;
        z-index: 3;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 7px;
        color: #8ba59e
    }

    .mini-thumbnail-placeholder span {
        font-size: 29px
    }

    .mini-thumbnail-placeholder small {
        font-size: 10px
    }

    .mini-pin {
        position: absolute;
        z-index: 4;
        top: 10px;
        left: 10px;
        padding: 5px 7px;
        border-radius: 4px;
        background: #fff3dc;
        color: #956513;
        font-size: 9px;
        font-weight: 700
    }

    .mini-card-body {
        padding: 14px
    }

    .mini-category {
        margin-bottom: 7px;
        color: #15966f;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: .5px;
        overflow-wrap: anywhere
    }

    .mini-card-body h3 {
        margin: 0 0 8px;
        color: #17284f;
        font-size: 14px;
        line-height: 1.45;
        overflow-wrap: anywhere
    }

    .mini-card-body>p {
        display: -webkit-box;
        margin: 0;
        overflow: hidden;
        color: #7d8797;
        font-size: 10px;
        line-height: 1.7;
        overflow-wrap: anywhere;
        -webkit-line-clamp: 4;
        -webkit-box-orient: vertical
    }

    .mini-meta {
        display: flex;
        flex-wrap: wrap;
        justify-content: space-between;
        align-items: center;
        gap: 8px;
        margin-top: 14px;
        padding-top: 11px;
        border-top: 1px solid #edf0f5
    }

    .mini-meta>span:first-child {
        color: #8a94a6;
        font-size: 9px
    }

    .mini-status {
        padding: 4px 6px;
        border-radius: 4px;
        background: #f1f5f9;
        color: #52606d;
        font-size: 8px;
        font-weight: 700
    }

    .mini-status.scheduled {
        background: #fff6df;
        color: #9a7515
    }

    .mini-status.published {
        background: #eaf7f3;
        color: #167d70
    }

    .mini-status.archived {
        background: #fceeee;
        color: #b34b4b
    }

    .preview-full-button {
        width: 100%;
        justify-content: center;
        margin-top: 12px
    }

    .checklist-card h3 {
        margin-bottom: 5px
    }

    .checklist-card>p {
        margin-bottom: 15px
    }

    .checklist-item {
        display: flex;
        align-items: center;
        gap: 9px;
        margin: 12px 0;
        color: #667187;
        font-size: 10px
    }

    .check-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 17px;
        height: 17px;
        border: 1px solid #d7dfe8;
        border-radius: 50%;
        color: #9aa4b3;
        font-size: 11px
    }

    .checklist-item.complete .check-icon {
        border-color: #bfe8d8;
        background: #eaf7f3;
        color: #15966f
    }

    .checklist-item.complete {
        color: #34415c
    }

    .visibility-card {
        background: #f9fbfd
    }

    .visibility-heading {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px
    }

    .visibility-heading strong {
        color: #34415c;
        font-size: 11px
    }

    .visibility-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #9aa4b3
    }

    .visibility-dot.scheduled {
        background: #d9a62e
    }

    .visibility-dot.published {
        background: #15966f
    }

    .visibility-dot.archived {
        background: #c94a4a
    }

    .visibility-card>p {
        margin: 0;
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.7
    }

    .reader-preview-modal {
        position: fixed;
        inset: 0;
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 24px
    }

    .reader-preview-modal.open {
        display: flex
    }

    .reader-preview-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(13, 24, 46, .68);
        backdrop-filter: blur(3px)
    }

    .reader-preview-dialog {
        position: relative;
        display: flex;
        flex-direction: column;
        width: min(100%, 1000px);
        max-height: min(92vh, 1000px);
        overflow: hidden;
        border-radius: 13px;
        background: #f5f7fb;
        box-shadow: 0 24px 90px rgba(0, 0, 0, .25)
    }

    .reader-preview-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        padding: 16px 22px;
        border-bottom: 1px solid #e5eaf0;
        background: #fff
    }

    .reader-brand {
        display: flex;
        align-items: center;
        gap: 11px;
        min-width: 0
    }

    .reader-brand>img,
    .reader-logo-fallback {
        flex-shrink: 0;
        width: 42px;
        height: 42px;
        object-fit: contain;
        border-radius: 8px
    }

    .reader-logo-fallback {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #17284f;
        color: #fff;
        font-size: 17px;
        font-weight: 700
    }

    .reader-preview-eyebrow {
        color: #15966f;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 1px
    }

    .reader-preview-toolbar h3 {
        margin: 5px 0 0;
        color: #17284f;
        font-size: 15px
    }

    .reader-close-button {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        width: 35px;
        height: 35px;
        border: 1px solid #e0e6ee;
        border-radius: 8px;
        background: #fff;
        color: #34415c;
        font-size: 23px;
        cursor: pointer
    }

    .reader-preview-scroll {
        flex: 1;
        min-height: 0;
        overflow-y: auto
    }

    .reader-article {
        width: min(100%, 790px);
        min-height: 100%;
        margin: 0 auto;
        background: #fff
    }

    .reader-cover {
        height: 270px
    }

    .reader-article-content {
        padding: 40px 50px 55px
    }

    .reader-category {
        margin-bottom: 12px;
        color: #15966f;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase
    }

    .reader-article h1 {
        margin: 0 0 22px;
        color: #17284f;
        font-size: clamp(25px, 4vw, 37px);
        line-height: 1.25;
        letter-spacing: -.6px;
        overflow-wrap: anywhere
    }

    .reader-byline {
        display: flex;
        align-items: center;
        gap: 11px;
        padding-bottom: 24px;
        border-bottom: 1px solid #e9edf3
    }

    .reader-author-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: #eaf7f3;
        color: #167d70;
        font-size: 13px;
        font-weight: 700
    }

    .reader-byline strong,
    .reader-byline span {
        display: block
    }

    .reader-byline strong {
        color: #34415c;
        font-size: 11px
    }

    .reader-byline div span {
        margin-top: 4px;
        color: #8a94a6;
        font-size: 10px
    }

    .reader-pinned-label {
        margin-top: 18px;
        color: #a56b16;
        font-size: 11px;
        font-weight: 700
    }

    .reader-summary {
        margin: 25px 0;
        color: #59667b;
        font-size: 15px;
        font-weight: 500;
        line-height: 1.8;
        white-space: pre-wrap;
        overflow-wrap: anywhere
    }

    .reader-body {
        color: #3e4a60;
        font-size: 14px;
        line-height: 1.9;
        overflow-wrap: anywhere
    }

    .reader-body h2,
    .reader-body h3,
    .reader-body h4 {
        margin: 28px 0 12px
    }

    .reader-body p {
        margin: 0 0 17px
    }

    .reader-body ul,
    .reader-body ol {
        padding-left: 24px;
        margin: 0 0 20px
    }

    .reader-body a {
        color: #167d70;
        text-decoration: underline
    }

    .reader-attachments-section {
        margin-top: 36px;
        padding-top: 25px;
        border-top: 1px solid #e9edf3
    }

    .reader-attachments-section h2 {
        margin: 0 0 16px;
        color: #17284f;
        font-size: 16px
    }

    .reader-attachment {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        margin-bottom: 9px;
        border: 1px solid #e5eaf0;
        border-radius: 8px
    }

    .reader-attachment-icon {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 37px;
        height: 40px;
        flex-shrink: 0;
        border-radius: 7px;
        background: #edf2f8;
        color: #34415c;
        font-size: 9px;
        font-weight: 700
    }

    .reader-attachment-name {
        min-width: 0;
        color: #34415c;
        font-size: 11px;
        overflow-wrap: anywhere
    }

    .reader-preview-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 16px;
        padding: 14px 22px;
        border-top: 1px solid #e5eaf0;
        background: #fff
    }

    .reader-preview-footer>span {
        color: #8a94a6;
        font-size: 10px;
        line-height: 1.6
    }

    @media(max-width:1100px) {
        .announcement-create-layout {
            grid-template-columns: minmax(0, 1fr) 270px;
            gap: 15px
        }

        .announcement-side-panel {
            top: 75px
        }
    }

    @media(max-width:900px) {
        .announcement-create-layout {
            grid-template-columns: minmax(0, 1fr)
        }

        .announcement-side-panel {
            position: static;
            grid-template-columns: repeat(2, minmax(0, 1fr))
        }

        .side-panel-card:first-child {
            grid-row: span 2
        }
    }

    @media(max-width:600px) {
        .announcement-side-panel {
            grid-template-columns: minmax(0, 1fr)
        }

        .side-panel-card:first-child {
            grid-row: auto
        }

        .announcement-form-card .card-head {
            flex-direction: column
        }

        .thumbnail-settings-grid {
            grid-template-columns: minmax(0, 1fr)
        }

        .thumbnail-file-bar {
            align-items: flex-start;
            flex-direction: column
        }

        .form-actions {
            flex-direction: column-reverse
        }

        .form-actions>* {
            width: 100%;
            justify-content: center
        }

        .reader-preview-modal {
            padding: 0
        }

        .reader-preview-dialog {
            width: 100%;
            height: 100%;
            max-height: 100%;
            border-radius: 0
        }

        .reader-preview-toolbar {
            padding: 13px 16px
        }

        .reader-article-content {
            padding: 27px 22px 38px
        }

        .reader-cover {
            height: 200px
        }

        .reader-preview-footer {
            align-items: flex-start;
            flex-direction: column;
            padding: 13px 16px
        }

        .field-footer {
            flex-direction: column
        }

        .table-tools-hint {
            width: 100%
        }

        .selected-file {
            flex-wrap: wrap
        }

        .file-preview-pdf {
            width: 100%;
            height: 110px;
            order: 3
        }

        .selected-file-info {
            min-width: calc(100% - 110px)
        }
    }

    /* ---------- Edit page additions ---------- */
    .existing-files-block {
        margin-bottom: 18px;
        padding: 14px;
        border: 1px solid #dce2eb;
        border-radius: 12px;
        background: #f9fafc;
    }

    .existing-files-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin-bottom: 10px;
        color: #17284f;
        font-size: 13px;
    }

    .existing-files-heading span {
        color: #64748b;
        font-size: 12px;
    }

    .selected-file.is-removed {
        opacity: .6;
        background: #fff5f5;
    }

    .selected-file.is-removed .selected-file-info strong {
        text-decoration: line-through;
    }

    .removed-badge {
        color: #b42318;
        font-weight: 700;
    }

    .thumbnail-removed-notice {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 6px;
        padding: 10px 14px;
        border: 1px solid #f5c2c0;
        border-radius: 10px;
        background: #fff5f5;
        color: #b42318;
        font-size: 12px;
        font-weight: 600;
    }

    .thumbnail-removed-notice[hidden] {
        display: none;
    }

    .details-card h3 {
        margin: 0 0 12px;
    }

    .details-list {
        display: grid;
        gap: 10px;
        margin: 0;
    }

    .details-list div {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        font-size: 12px;
    }

    .details-list dt {
        color: #64748b;
    }

    .details-list dd {
        margin: 0;
        color: #17284f;
        font-weight: 600;
        text-align: right;
        overflow-wrap: anywhere;
    }

    .details-slug {
        font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
        font-weight: 500 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('announcementEditForm');
        const title = document.getElementById('title');
        const category = document.getElementById('category');
        const tagSearch = document.getElementById('tagSearch');
        const summary = document.getElementById('summary');
        const contentField = document.getElementById('content');
        const editor = document.getElementById('contentEditor');
        const initialContent = document.getElementById('initialContent');
        const status = document.getElementById('status');
        const scheduledAt = document.getElementById('scheduled_at');
        const expiresAt = document.getElementById('expires_at');
        const pinned = document.getElementById('is_pinned');
        const thumbnailInput = document.getElementById('thumbnail');
        const attachmentInput = document.getElementById('attachmentFiles');
        const modal = document.getElementById('readerPreviewModal');

        const thumbnailPlaceholder = document.getElementById('thumbnailPlaceholder');
        const thumbnailWorkspace = document.getElementById('thumbnailWorkspace');
        const thumbnailPreview = document.getElementById('thumbnailPreview');
        const thumbnailBlurLayer = document.getElementById('thumbnailBlurLayer');
        const thumbnailSolidLayer = document.getElementById('thumbnailSolidLayer');
        const thumbnailFileName = document.getElementById('thumbnailFileName');
        const thumbnailFileSize = document.getElementById('thumbnailFileSize');
        const thumbnailSettings = document.getElementById('thumbnailSettings');
        const thumbnailFit = document.getElementById('thumbnailFit');
        const thumbnailPosition = document.getElementById('thumbnailPosition');
        const thumbnailBackground = document.getElementById('thumbnailBackground');
        const thumbnailBackgroundStyle = document.getElementById('thumbnailBackgroundStyle');
        const thumbnailGradientStart = document.getElementById('thumbnailGradientStart');
        const thumbnailGradientEnd = document.getElementById('thumbnailGradientEnd');

        const miniThumbnailWrap = document.getElementById('miniThumbnailWrap');
        const miniThumbnail = document.getElementById('miniThumbnail');
        const miniThumbnailBlur = document.getElementById('miniThumbnailBlur');
        const miniThumbnailSolid = document.getElementById('miniThumbnailSolid');
        const miniThumbnailPlaceholder = document.getElementById('miniThumbnailPlaceholder');

        const readerCoverWrap = document.getElementById('readerCoverWrap');
        const readerCover = document.getElementById('readerCover');
        const readerCoverBlur = document.getElementById('readerCoverBlur');
        const readerCoverSolid = document.getElementById('readerCoverSolid');

        const attachmentPreview = document.getElementById('attachmentFilesPreview');
        const attachmentSummary = document.getElementById('attachmentSummary');
        const attachmentWarning = document.getElementById('attachmentWarning');
        const editorImageInput = document.getElementById('editorImageInput');

        const MAX_THUMBNAIL_SIZE = 4 * 1024 * 1024;
        const MAX_ATTACHMENT_SIZE = 100 * 1024 * 1024;

        let thumbnailUrl = null;
        let thumbnailFile = null;
        let attachmentFiles = [];
        let attachmentObjectUrls = new Map();
        let editorSelection = null;
        let activeTable = null;
        let previewOpen = false;
        let attachmentPreviewOpen = false;
        let selectedTags = [];
        let tagSuggestions = [];
        let activeAttachmentUrl = null;

        const existingThumbnail = @json($existingThumbnail);
        const existingAttachments = @json($existingAttachments);
        const existingMedia = @json($existingMedia);

        let thumbnailRemoved = @json((bool) old('remove_thumbnail'));
        const removedAttachmentIds = new Set(@json(array_values((array) old('remove_attachments', []))));
        const removedMediaIds = new Set(@json(array_values((array) old('remove_media', []))));

        const publishedAtInitial = @json(optional($announcement->published_at)->format('Y-m-d\TH:i'));

        const statusDescriptions = {
            draft: 'Save the announcement privately and continue editing it later.',
            scheduled: 'Choose a future date and time. The announcement is published automatically when that time arrives.',
            published: 'Publish the announcement immediately. It stays visible until its expiration time, if one is set.',
            archived: 'Save the announcement as archived. It should not appear in the public announcement list.'
        };

        const visibilityCopy = {
            draft: ['Private draft', 'This announcement should not be visible in the public announcement list.'],
            scheduled: ['Scheduled publication', 'This announcement stays hidden and is published automatically at the scheduled time.'],
            published: ['Published announcement', 'This announcement is visible to readers as soon as it is saved, until it expires.'],
            archived: ['Archived announcement', 'This announcement should remain unavailable in the public announcement list.']
        };

        function escapeHtml(value) {
            return String(value).replace(/[&<>"']/g, function(character) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [character];
            });
        }

        function formatFileSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / (1024 * 1024)).toFixed(2) + ' MB';
        }

        function formatDate(value) {
            if (!value) return '';

            const date = new Date(value);

            if (Number.isNaN(date.getTime())) return '';

            return date.toLocaleString('en-GB', {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
        }

        function safePreviewHtml(html) {
            const parsed = new DOMParser().parseFromString(html || '', 'text/html');

            parsed.querySelectorAll('script,style,iframe,object,embed,form,svg,math,meta,link').forEach(function(node) {
                node.remove();
            });

            parsed.querySelectorAll('*').forEach(function(element) {
                Array.from(element.attributes).forEach(function(attribute) {
                    const name = attribute.name.toLowerCase();
                    const value = attribute.value.trim();

                    if (name.startsWith('on') || name === 'srcdoc') {
                        element.removeAttribute(attribute.name);
                        return;
                    }

                    if (['href', 'src', 'xlink:href', 'formaction'].includes(name)) {
                        const compact = value.replace(/[\u0000-\u0020]+/g, '').toLowerCase();

                        if (
                            compact.startsWith('javascript:') ||
                            compact.startsWith('vbscript:') ||
                            compact.startsWith('file:')
                        ) {
                            element.removeAttribute(attribute.name);
                        }

                        if (compact.startsWith('data:') && !compact.startsWith('data:image/')) {
                            element.removeAttribute(attribute.name);
                        }
                    }
                });
            });

            return parsed.body.innerHTML;
        }

        function syncContent() {
            contentField.value = editor.innerHTML.trim();

            document.getElementById('contentCount').textContent =
                editor.innerText.trim().length.toLocaleString('en-US') + ' characters';
        }

        function updateCount(input, counterId, max) {
            const counter = document.getElementById(counterId);

            if (!counter) return;

            counter.textContent = max ?
                input.value.length + ' / ' + max :
                input.value.length + ' characters';
        }

        function updateChecklist(id, complete) {
            const item = document.getElementById(id);

            if (!item) return;

            item.classList.toggle('complete', complete);

            const icon = item.querySelector('.check-icon');

            if (icon) {
                icon.textContent = complete ? '✓' : '○';
            }
        }

        function updateTagHiddenField() {
            category.value = selectedTags.join(', ');
        }

        function renderSelectedTags() {
            const container = document.getElementById('selectedTags');

            if (!container) return;

            container.replaceChildren();

            selectedTags.forEach(function(tag) {
                const chip = document.createElement('span');
                chip.className = 'tag-chip';

                const text = document.createElement('span');
                text.textContent = tag;

                const remove = document.createElement('button');
                remove.type = 'button';
                remove.setAttribute('aria-label', 'Remove ' + tag);
                remove.textContent = '×';

                remove.addEventListener('click', function() {
                    selectedTags = selectedTags.filter(function(item) {
                        return item.toLowerCase() !== tag.toLowerCase();
                    });

                    updateTagHiddenField();
                    renderSelectedTags();
                    renderTagRecommendations();
                    updatePreview();
                });

                chip.append(text, remove);
                container.appendChild(chip);
            });

            updateTagHiddenField();
        }

        function addTag(value) {
            const tag = String(value || '').trim().replace(/,/g, '');

            if (!tag) return;

            if (selectedTags.some(function(item) {
                    return item.toLowerCase() === tag.toLowerCase();
                })) {
                tagSearch.value = '';
                renderTagRecommendations();
                return;
            }

            selectedTags.push(tag);
            tagSearch.value = '';

            updateTagHiddenField();
            renderSelectedTags();
            renderTagRecommendations();
            updatePreview();
        }

        function renderTagRecommendations() {
            const container = document.getElementById('tagRecommendations');

            if (!container || !tagSearch) return;

            const search = tagSearch.value.trim().toLowerCase();

            container.replaceChildren();

            const filtered = tagSuggestions.filter(function(item) {
                const name = typeof item === 'string' ?
                    item :
                    (item.name || item.tag || item.category || '');

                return name &&
                    name.toLowerCase().includes(search) &&
                    !selectedTags.some(function(selected) {
                        return selected.toLowerCase() === name.toLowerCase();
                    });
            });

            if (!filtered.length) {
                const empty = document.createElement('span');
                empty.className = 'tag-empty-message';
                empty.textContent = search ?
                    'No matching tags. Press Enter or Add to create this tag.' :
                    'No previously used tags were supplied.';

                container.appendChild(empty);
                return;
            }

            filtered.forEach(function(item) {
                const name = typeof item === 'string' ?
                    item :
                    (item.name || item.tag || item.category || '');

                const count = typeof item === 'object' && item !== null ?
                    (item.usage_count ?? item.count ?? item.uses ?? null) :
                    null;

                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'tag-suggestion';

                const label = document.createElement('span');
                label.textContent = name;

                button.appendChild(label);

                if (count !== null && count !== undefined && count !== '') {
                    const usage = document.createElement('span');
                    usage.className = 'tag-suggestion-count';
                    usage.textContent = '· ' + count;
                    button.appendChild(usage);
                }

                button.addEventListener('click', function() {
                    addTag(name);
                });

                container.appendChild(button);
            });
        }

        function applyBackgroundStyle() {
            const style = thumbnailBackgroundStyle.value;
            const solid = thumbnailBackground.value;
            const start = thumbnailGradientStart.value;
            const end = thumbnailGradientEnd.value;
            const isFit = thumbnailFit.value === 'fit';

            [thumbnailSolidLayer, miniThumbnailSolid, readerCoverSolid].forEach(function(layer) {
                if (!layer) return;

                layer.hidden = !isFit || style === 'transparent';

                if (style === 'gradient') {
                    layer.style.background = 'linear-gradient(135deg, ' + start + ', ' + end + ')';
                } else {
                    layer.style.background = solid;
                }
            });

            const solidGroup = document.getElementById('thumbnailSolidColorGroup');
            const gradientStartGroup = document.getElementById('thumbnailGradientStartGroup');
            const gradientEndGroup = document.getElementById('thumbnailGradientEndGroup');

            if (solidGroup) {
                solidGroup.hidden = !isFit || style !== 'solid';
            }

            if (gradientStartGroup) {
                gradientStartGroup.hidden = !isFit || style !== 'gradient';
            }

            if (gradientEndGroup) {
                gradientEndGroup.hidden = !isFit || style !== 'gradient';
            }

            const backgroundValue = document.getElementById('thumbnailBackgroundValue');
            const gradientStartValue = document.getElementById('thumbnailGradientStartValue');
            const gradientEndValue = document.getElementById('thumbnailGradientEndValue');

            if (backgroundValue) backgroundValue.textContent = solid.toUpperCase();
            if (gradientStartValue) gradientStartValue.textContent = start.toUpperCase();
            if (gradientEndValue) gradientEndValue.textContent = end.toUpperCase();
        }

        function applyThumbnailSettings() {
            const mode = thumbnailFit.value;
            const position = thumbnailPosition.value;

            const modeClass = {
                cover: 'mode-cover',
                fit: 'mode-fit',
                blur: 'mode-blur'
            } [mode] || 'mode-cover';

            [
                document.getElementById('thumbnailStage'),
                miniThumbnailWrap,
                readerCoverWrap
            ].forEach(function(element) {
                if (!element) return;

                element.classList.remove('mode-cover', 'mode-fit', 'mode-blur');
                element.classList.add(modeClass);
            });

            [thumbnailPreview, miniThumbnail, readerCover].forEach(function(element) {
                if (!element) return;

                element.classList.remove(
                    'position-center',
                    'position-top',
                    'position-bottom',
                    'position-left',
                    'position-right'
                );

                element.classList.add('position-' + position);
            });

            const isBlur = mode === 'blur';
            const isFit = mode === 'fit';

            [thumbnailBlurLayer, miniThumbnailBlur, readerCoverBlur].forEach(function(element) {
                if (element) {
                    element.hidden = !isBlur;
                }
            });

            const positionGroup = document.getElementById('thumbnailPositionGroup');
            const backgroundStyleGroup = document.getElementById('thumbnailBackgroundStyleGroup');

            if (positionGroup) {
                positionGroup.hidden = false;
            }

            if (backgroundStyleGroup) {
                backgroundStyleGroup.hidden = !isFit;
            }

            if (!isFit) {
                [
                    'thumbnailSolidColorGroup',
                    'thumbnailGradientStartGroup',
                    'thumbnailGradientEndGroup'
                ].forEach(function(id) {
                    const element = document.getElementById(id);

                    if (element) {
                        element.hidden = true;
                    }
                });

                [thumbnailSolidLayer, miniThumbnailSolid, readerCoverSolid].forEach(function(element) {
                    if (element) {
                        element.hidden = true;
                    }
                });
            } else {
                applyBackgroundStyle();
            }
        }

        function revokeThumbnailUrl() {
            if (thumbnailUrl) {
                URL.revokeObjectURL(thumbnailUrl);
                thumbnailUrl = null;
            }
        }

        function hasThumbnail() {
            return Boolean(thumbnailFile) || Boolean(existingThumbnail && !thumbnailRemoved);
        }

        function showThumbnailImage(url, name, sizeText, canDiscard) {
            thumbnailPreview.src = url;
            thumbnailBlurLayer.src = url;

            thumbnailFileName.textContent = name;
            thumbnailFileSize.textContent = sizeText;

            miniThumbnail.src = url;
            miniThumbnailBlur.src = url;

            readerCover.src = url;
            readerCoverBlur.src = url;

            document.getElementById('removeThumbnail').textContent = canDiscard ? 'Discard' : 'Remove';

            thumbnailWorkspace.hidden = false;
            thumbnailPlaceholder.hidden = true;
            thumbnailSettings.hidden = false;
            miniThumbnail.hidden = false;
            miniThumbnailPlaceholder.hidden = true;
            readerCoverWrap.hidden = false;

            applyThumbnailSettings();
        }

        function hideThumbnailImage() {
            thumbnailWorkspace.hidden = true;
            thumbnailPlaceholder.hidden = false;
            thumbnailSettings.hidden = true;
            miniThumbnail.hidden = true;
            miniThumbnailBlur.hidden = true;
            miniThumbnailSolid.hidden = true;
            miniThumbnailPlaceholder.hidden = false;
            readerCoverWrap.hidden = true;
        }

        function updateThumbnailPreview(file) {
            revokeThumbnailUrl();

            thumbnailFile = file || null;

            document.getElementById('removeThumbnailField').value = thumbnailRemoved ? '1' : '0';

            if (thumbnailFile) {
                thumbnailUrl = URL.createObjectURL(thumbnailFile);

                showThumbnailImage(
                    thumbnailUrl,
                    thumbnailFile.name,
                    formatFileSize(thumbnailFile.size),
                    Boolean(existingThumbnail && !thumbnailRemoved)
                );
            } else if (existingThumbnail && !thumbnailRemoved) {
                showThumbnailImage(
                    existingThumbnail.url,
                    existingThumbnail.name,
                    'Current thumbnail',
                    false
                );
            } else {
                hideThumbnailImage();
            }

            document.getElementById('thumbnailRemovedNotice').hidden =
                !(existingThumbnail && thumbnailRemoved && !thumbnailFile);

            updateChecklist('checkThumbnail', hasThumbnail());
        }

        function updatePinSettings() {
            document.getElementById('pinOrderGroup').hidden = !pinned.checked;
            document.getElementById('miniPin').hidden = !pinned.checked;
            document.getElementById('readerPinned').hidden = !pinned.checked;
        }

        function updateStatusFields() {
            const value = status.value;

            document.getElementById('scheduledSettings').hidden = value !== 'scheduled';
            document.getElementById('publishedSettings').hidden = value !== 'published';
            document.getElementById('archivedSettings').hidden = value !== 'archived';

            scheduledAt.required = value === 'scheduled';

            document.getElementById('expirationGroup').hidden =
                value === 'draft' || value === 'archived';

            document.getElementById('statusDescription').textContent =
                statusDescriptions[value] || statusDescriptions.draft;

            const labels = {
                draft: 'Save Draft Changes',
                scheduled: 'Save Schedule',
                published: 'Save & Publish',
                archived: 'Save to Archive'
            };

            document.getElementById('submitAnnouncementButton').textContent =
                labels[value] || labels.draft;

            const visibility = visibilityCopy[value] || visibilityCopy.draft;

            document.getElementById('visibilityTitle').textContent = visibility[0];
            document.getElementById('visibilityDescription').textContent = visibility[1];
            document.getElementById('visibilityDot').className = 'visibility-dot ' + value;

            const miniStatus = document.getElementById('miniStatus');

            miniStatus.textContent = value.toUpperCase();
            miniStatus.className = 'mini-status ' + value;

            updatePreview();
        }

        function updatePreview() {
            const titleText = title.value.trim();
            const categoryText = selectedTags.length ?
                selectedTags.join(', ') :
                'General Announcement';

            const summaryText = summary.value.trim();

            let formattedDate = '';

            if (status.value === 'scheduled') {
                formattedDate = formatDate(scheduledAt.value);
            } else if (status.value === 'published') {
                formattedDate = publishedAtInitial ?
                    formatDate(publishedAtInitial) :
                    'Published when saved';
            }

            document.getElementById('miniTitle').textContent =
                titleText || 'Your announcement title';

            document.getElementById('miniCategory').textContent =
                categoryText.toUpperCase();

            document.getElementById('miniSummary').textContent =
                summaryText || 'Your short summary will appear here to help readers understand the announcement.';

            document.getElementById('miniDate').textContent =
                formattedDate || 'Publication date not set';

            document.getElementById('readerTitle').textContent =
                titleText || 'Your announcement title';

            document.getElementById('readerCategory').textContent = categoryText;

            document.getElementById('readerSummary').textContent =
                summaryText || 'Your announcement summary will appear here.';

            document.getElementById('readerPublicationDate').textContent =
                formattedDate || 'Publication date not set';

            document.getElementById('readerContent').innerHTML =
                safePreviewHtml(editor.innerHTML);

            updateChecklist('checkTitle', titleText.length > 0);
            updateChecklist('checkSummary', summaryText.length > 0);
            updateChecklist('checkContent', editor.innerText.trim().length > 0);
            updateChecklist('checkThumbnail', hasThumbnail());
            updateChecklist('checkPublication', Boolean(status.value));

            updatePinSettings();
        }

        function fileExtension(file) {
            const parts = file.name.split('.');

            return parts.length > 1 ?
                parts.pop().slice(0, 8).toUpperCase() :
                'FILE';
        }

        function fileKind(file) {
            const mime = file.type || '';

            if (mime.startsWith('image/')) return 'image';

            if (mime === 'application/pdf' || /\.pdf$/i.test(file.name)) {
                return 'pdf';
            }

            if (mime.startsWith('video/') || /\.(mp4|webm)$/i.test(file.name)) return 'video';
            if (mime.startsWith('audio/') || /\.(mp3|wav)$/i.test(file.name)) return 'audio';
            if (/\.(jpe?g|png|webp|gif)$/i.test(file.name)) return 'image';

            if (/\.xlsx?$/i.test(file.name)) return 'excel';
            if (/\.docx?$/i.test(file.name)) return 'word';
            if (/\.pptx?$/i.test(file.name)) return 'powerpoint';

            if (/\.(zip|rar|7z|tar|gz)$/i.test(file.name)) {
                return 'archive';
            }

            return 'file';
        }

        function getAttachmentUrl(file) {
            if (file.__url) {
                return file.__url;
            }

            if (!attachmentObjectUrls.has(file)) {
                attachmentObjectUrls.set(file, URL.createObjectURL(file));
            }

            return attachmentObjectUrls.get(file);
        }

        function revokeAttachmentUrl(file) {
            const url = attachmentObjectUrls.get(file);

            if (url) {
                URL.revokeObjectURL(url);
                attachmentObjectUrls.delete(file);
            }
        }

        function syncAttachmentInput() {
            const transfer = new DataTransfer();

            attachmentFiles.forEach(function(file) {
                transfer.items.add(file);
            });

            attachmentInput.files = transfer.files;
        }

        function showAttachmentWarning(message) {
            attachmentWarning.textContent = message;
            attachmentWarning.hidden = !message;
        }

        function createAttachmentPreviewModal() {
            let existing = document.getElementById('attachmentPreviewModal');

            if (existing) {
                return existing;
            }

            const modalElement = document.createElement('div');

            modalElement.id = 'attachmentPreviewModal';
            modalElement.setAttribute('aria-hidden', 'true');

            modalElement.style.cssText = [
                'display:none',
                'position:fixed',
                'inset:0',
                'z-index:10050',
                'align-items:center',
                'justify-content:center',
                'padding:24px',
                'background:rgba(15,23,42,.72)'
            ].join(';');

            modalElement.innerHTML = `
                    <div
                        role="dialog"
                        aria-modal="true"
                        aria-labelledby="attachmentPreviewTitle"
                        style="
                            display:flex;
                            flex-direction:column;
                            width:min(1100px,100%);
                            max-height:92vh;
                            overflow:hidden;
                            background:#fff;
                            border-radius:16px;
                            box-shadow:0 24px 80px rgba(0,0,0,.25);
                        "
                    >
                        <div style="
                            display:flex;
                            align-items:center;
                            justify-content:space-between;
                            gap:16px;
                            padding:18px 22px;
                            border-bottom:1px solid #e5e7eb;
                        ">
                            <div style="min-width:0;">
                                <h2
                                    id="attachmentPreviewTitle"
                                    style="
                                        margin:0;
                                        color:#17284f;
                                        font-size:17px;
                                        font-weight:700;
                                        overflow-wrap:anywhere;
                                    "
                                >Attachment Preview</h2>
                                <p
                                    id="attachmentPreviewMeta"
                                    style="
                                        margin:5px 0 0;
                                        color:#64748b;
                                        font-size:12px;
                                    "
                                ></p>
                            </div>

                            <div style="
                                display:flex;
                                align-items:center;
                                gap:8px;
                                flex-shrink:0;
                            ">
                                <a
                                    id="attachmentPreviewDownload"
                                    href="#"
                                    download
                                    style="
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        padding:9px 13px;
                                        border:1px solid #dce2eb;
                                        border-radius:8px;
                                        color:#17284f;
                                        background:#fff;
                                        font-size:12px;
                                        font-weight:600;
                                        text-decoration:none;
                                    "
                                >Download</a>

                                <button
                                    id="closeAttachmentPreview"
                                    type="button"
                                    aria-label="Close attachment preview"
                                    style="
                                        display:inline-flex;
                                        align-items:center;
                                        justify-content:center;
                                        width:36px;
                                        height:36px;
                                        border:0;
                                        border-radius:8px;
                                        background:#f1f5f9;
                                        color:#17284f;
                                        font-size:23px;
                                        cursor:pointer;
                                    "
                                >×</button>
                            </div>
                        </div>

                        <div
                            id="attachmentPreviewBody"
                            style="
                                flex:1;
                                min-height:220px;
                                overflow:auto;
                                padding:20px;
                                background:#f5f7fb;
                            "
                        ></div>
                    </div>
                `;

            document.body.appendChild(modalElement);

            modalElement.addEventListener('click', function(event) {
                if (event.target === modalElement) {
                    closeAttachmentPreview();
                }
            });

            document.getElementById('closeAttachmentPreview').addEventListener('click', function() {
                closeAttachmentPreview();
            });

            return modalElement;
        }

        function openAttachmentPreview(file) {
            if (!file) return;

            const attachmentModal = createAttachmentPreviewModal();
            const titleElement = document.getElementById('attachmentPreviewTitle');
            const metaElement = document.getElementById('attachmentPreviewMeta');
            const bodyElement = document.getElementById('attachmentPreviewBody');
            const downloadElement = document.getElementById('attachmentPreviewDownload');

            const kind = fileKind(file);
            const url = getAttachmentUrl(file);

            activeAttachmentUrl = url;

            titleElement.textContent = file.name;
            metaElement.textContent =
                fileExtension(file) + ' · ' + formatFileSize(file.size);

            downloadElement.href = url;
            downloadElement.download = file.name;
            downloadElement.hidden = false;

            bodyElement.replaceChildren();

            if (kind === 'image') {
                const image = document.createElement('img');

                image.src = url;
                image.alt = file.name;

                image.style.cssText = [
                    'display:block',
                    'max-width:100%',
                    'max-height:70vh',
                    'width:auto',
                    'height:auto',
                    'margin:0 auto',
                    'object-fit:contain',
                    'border-radius:8px',
                    'background:#fff'
                ].join(';');

                bodyElement.appendChild(image);
            } else if (kind === 'pdf') {
                const frame = document.createElement('iframe');

                frame.src = url + '#toolbar=1&navpanes=0&view=FitH';
                frame.title = 'PDF preview: ' + file.name;

                frame.style.cssText = [
                    'display:block',
                    'width:100%',
                    'height:70vh',
                    'min-height:420px',
                    'border:1px solid #dce2eb',
                    'border-radius:8px',
                    'background:#fff'
                ].join(';');

                bodyElement.appendChild(frame);
            } else if (kind === 'video') {
                const video = document.createElement('video');

                video.src = url;
                video.controls = true;
                video.preload = 'metadata';

                video.style.cssText = [
                    'display:block',
                    'max-width:100%',
                    'max-height:70vh',
                    'margin:0 auto',
                    'border-radius:8px',
                    'background:#111'
                ].join(';');

                bodyElement.appendChild(video);
            } else if (kind === 'audio') {
                const wrapper = document.createElement('div');

                wrapper.style.cssText = [
                    'display:flex',
                    'flex-direction:column',
                    'align-items:center',
                    'justify-content:center',
                    'gap:20px',
                    'min-height:260px',
                    'padding:30px'
                ].join(';');

                const icon = document.createElement('div');
                icon.textContent = '♫';
                icon.style.cssText = 'font-size:52px;color:#17284f';

                const audio = document.createElement('audio');
                audio.src = url;
                audio.controls = true;
                audio.style.width = 'min(500px,100%)';

                wrapper.append(icon, audio);
                bodyElement.appendChild(wrapper);
            } else {
                const wrapper = document.createElement('div');

                wrapper.style.cssText = [
                    'display:flex',
                    'flex-direction:column',
                    'align-items:center',
                    'justify-content:center',
                    'gap:12px',
                    'min-height:300px',
                    'padding:32px',
                    'text-align:center',
                    'background:#fff',
                    'border:1px solid #e5e7eb',
                    'border-radius:12px'
                ].join(';');

                const icon = document.createElement('div');
                icon.textContent = fileExtension(file);
                icon.style.cssText = [
                    'display:flex',
                    'align-items:center',
                    'justify-content:center',
                    'min-width:72px',
                    'height:72px',
                    'padding:0 12px',
                    'border-radius:12px',
                    'background:#eef2ff',
                    'color:#17284f',
                    'font-size:15px',
                    'font-weight:800'
                ].join(';');

                const heading = document.createElement('h3');
                heading.textContent = 'Preview is not available in this browser';
                heading.style.cssText = 'margin:8px 0 0;color:#17284f;font-size:16px';

                const description = document.createElement('p');
                description.textContent =
                    'This file format cannot be displayed directly here. Download the file to open it with a compatible application.';
                description.style.cssText = [
                    'max-width:500px',
                    'margin:0',
                    'color:#64748b',
                    'font-size:13px',
                    'line-height:1.7'
                ].join(';');

                const fileInfo = document.createElement('p');
                fileInfo.textContent = file.name + ' · ' + formatFileSize(file.size);
                fileInfo.style.cssText = [
                    'max-width:100%',
                    'margin:4px 0 0',
                    'color:#334155',
                    'font-size:12px',
                    'overflow-wrap:anywhere'
                ].join(';');

                const downloadButton = document.createElement('a');
                downloadButton.href = url;
                downloadButton.download = file.name;
                downloadButton.textContent = 'Download File';
                downloadButton.style.cssText = [
                    'display:inline-flex',
                    'align-items:center',
                    'justify-content:center',
                    'margin-top:8px',
                    'padding:11px 18px',
                    'border-radius:8px',
                    'background:#17284f',
                    'color:#fff',
                    'font-size:13px',
                    'font-weight:600',
                    'text-decoration:none'
                ].join(';');

                wrapper.append(icon, heading, description, fileInfo, downloadButton);
                bodyElement.appendChild(wrapper);
            }

            attachmentModal.style.display = 'flex';
            attachmentModal.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            attachmentPreviewOpen = true;

            const closeButton = document.getElementById('closeAttachmentPreview');

            if (closeButton) {
                closeButton.focus();
            }
        }

        function closeAttachmentPreview() {
            const attachmentModal = document.getElementById('attachmentPreviewModal');

            if (!attachmentModal || !attachmentPreviewOpen) return;

            const bodyElement = document.getElementById('attachmentPreviewBody');

            if (bodyElement) {
                bodyElement.querySelectorAll('video,audio').forEach(function(media) {
                    media.pause();
                });

                bodyElement.replaceChildren();
            }

            attachmentModal.style.display = 'none';
            attachmentModal.setAttribute('aria-hidden', 'true');

            attachmentPreviewOpen = false;
            activeAttachmentUrl = null;

            if (!previewOpen) {
                document.body.style.overflow = '';
            }
        }

        function renderAttachments() {
            attachmentPreview.replaceChildren();

            let totalSize = 0;

            attachmentFiles.forEach(function(file, index) {
                totalSize += file.size;

                const row = document.createElement('div');
                row.className = 'selected-file';

                const kind = fileKind(file);

                if (kind === 'image') {
                    const image = document.createElement('img');

                    image.className = 'file-preview-thumb';
                    image.src = getAttachmentUrl(file);
                    image.alt = file.name;
                    image.title = file.name;

                    row.appendChild(image);
                } else {
                    const icon = document.createElement('div');

                    icon.className = 'file-type-icon';
                    icon.textContent = fileExtension(file);

                    row.appendChild(icon);
                }

                const info = document.createElement('div');
                info.className = 'selected-file-info';

                const name = document.createElement('strong');
                name.textContent = file.name;
                name.title = file.name;

                const type = document.createElement('span');
                type.textContent =
                    (file.type || 'Unknown file type') + ' · ' + formatFileSize(file.size);

                info.append(name, type);

                const actions = document.createElement('div');

                actions.style.cssText = [
                    'display:flex',
                    'align-items:center',
                    'gap:8px',
                    'flex-shrink:0'
                ].join(';');

                const previewButton = document.createElement('button');

                previewButton.type = 'button';
                previewButton.className = 'preview-file-button';
                previewButton.textContent = 'Preview';
                previewButton.setAttribute('aria-label', 'Preview ' + file.name);

                previewButton.style.cssText = [
                    'padding:7px 11px',
                    'border:1px solid #dce2eb',
                    'border-radius:7px',
                    'background:#fff',
                    'color:#17284f',
                    'font-size:12px',
                    'font-weight:600',
                    'cursor:pointer'
                ].join(';');

                previewButton.addEventListener('click', function() {
                    openAttachmentPreview(file);
                });

                const remove = document.createElement('button');

                remove.type = 'button';
                remove.className = 'remove-file-button';
                remove.textContent = 'Remove';

                remove.addEventListener('click', function() {
                    const removed = attachmentFiles.splice(index, 1)[0];

                    if (removed) {
                        const currentPreview = document.getElementById('attachmentPreviewModal');

                        if (
                            currentPreview &&
                            attachmentPreviewOpen &&
                            attachmentFiles.indexOf(removed) === -1 &&
                            activeAttachmentUrl === attachmentObjectUrls.get(removed)
                        ) {
                            closeAttachmentPreview();
                        }

                        revokeAttachmentUrl(removed);
                    }

                    syncAttachmentInput();
                    renderAttachments();
                    updateReaderAttachments();
                });

                actions.append(previewButton, remove);
                row.append(info, actions);
                attachmentPreview.appendChild(row);
            });

            attachmentSummary.hidden = attachmentFiles.length === 0;

            document.getElementById('attachmentCount').textContent =
                attachmentFiles.length + (attachmentFiles.length === 1 ? ' file' : ' files');

            document.getElementById('attachmentTotalSize').textContent =
                formatFileSize(totalSize) + ' / 100 MB';
        }

        function addAttachments(files) {
            const existing = new Set(attachmentFiles.map(function(file) {
                return file.name + ':' + file.size + ':' + file.lastModified;
            }));

            let currentSize = attachmentFiles.reduce(function(total, file) {
                return total + file.size;
            }, 0);

            const errors = [];

            Array.from(files).forEach(function(file) {
                const key = file.name + ':' + file.size + ':' + file.lastModified;

                if (existing.has(key)) return;

                if (currentSize + file.size > MAX_ATTACHMENT_SIZE) {
                    errors.push(file.name + ': adding this file would exceed the 100 MB total limit.');
                    return;
                }

                attachmentFiles.push(file);
                existing.add(key);
                currentSize += file.size;
            });

            syncAttachmentInput();
            renderAttachments();
            updateReaderAttachments();

            showAttachmentWarning(errors.join(' '));
        }

        function saveEditorSelection() {
            const selection = window.getSelection();

            if (selection && selection.rangeCount && editor.contains(selection.anchorNode)) {
                editorSelection = selection.getRangeAt(0).cloneRange();
            }
        }

        function restoreEditorSelection() {
            editor.focus();

            const selection = window.getSelection();

            if (!selection) return;

            selection.removeAllRanges();

            if (editorSelection) {
                try {
                    selection.addRange(editorSelection);
                } catch (error) {
                    editorSelection = null;
                }
            }
        }

        function execEditorCommand(command, value) {
            restoreEditorSelection();

            document.execCommand(command, false, value || null);

            saveEditorSelection();
            syncContent();
            updatePreview();
        }

        function insertHtmlAtCursor(html) {
            restoreEditorSelection();

            document.execCommand('insertHTML', false, html);

            saveEditorSelection();
            syncContent();
            updatePreview();
        }

        function getCurrentTable() {
            const selection = window.getSelection();

            if (!selection || !selection.rangeCount) return null;

            let node = selection.anchorNode;

            if (node && node.nodeType === Node.TEXT_NODE) {
                node = node.parentElement;
            }

            if (!node || !editor.contains(node)) return null;

            return node.closest('table');
        }

        function getSelectedCell(table) {
            const selection = window.getSelection();
            let node = selection && selection.anchorNode;

            if (node && node.nodeType === Node.TEXT_NODE) {
                node = node.parentElement;
            }

            const cell = node && node.closest ?
                node.closest('td, th') :
                null;

            return cell && cell.closest('table') === table ? cell : null;
        }

        function tableCellCount(table) {
            if (!table || !table.rows.length) return 0;

            return Math.max.apply(null, Array.from(table.rows).map(function(row) {
                return row.cells.length;
            }));
        }

        function rgbToHex(color) {
            if (!color) return '#ffffff';
            if (color.startsWith('#')) return color;

            const match = color.match(/\d+/g);

            if (!match || match.length < 3) return '#ffffff';

            return '#' + match.slice(0, 3).map(function(number) {
                return Number(number).toString(16).padStart(2, '0');
            }).join('');
        }

        function updateTableTools() {
            activeTable = getCurrentTable();

            const tools = document.getElementById('editorTableTools');

            tools.hidden = !activeTable;

            if (!activeTable) return;

            const header = activeTable.querySelector('th');
            const cell = activeTable.querySelector('td');

            if (header) {
                const headerBg = header.style.backgroundColor;
                const headerColor = header.style.color;

                if (headerBg) {
                    document.getElementById('tableHeaderBackground').value = rgbToHex(headerBg);
                }

                if (headerColor) {
                    document.getElementById('tableHeaderColor').value = rgbToHex(headerColor);
                }
            }

            if (cell) {
                const cellBg = cell.style.backgroundColor;
                const cellColor = cell.style.color;

                if (cellBg) {
                    document.getElementById('tableCellBackground').value = rgbToHex(cellBg);
                }

                if (cellColor) {
                    document.getElementById('tableCellColor').value = rgbToHex(cellColor);
                }
            }
        }

        function applyTableStyles(table) {
            if (!table) return;

            const headerBg = document.getElementById('tableHeaderBackground').value;
            const headerColor = document.getElementById('tableHeaderColor').value;
            const cellBg = document.getElementById('tableCellBackground').value;
            const cellColor = document.getElementById('tableCellColor').value;

            table.dataset.headerBackground = headerBg;
            table.dataset.headerColor = headerColor;
            table.dataset.cellBackground = cellBg;
            table.dataset.cellColor = cellColor;

            table.style.width = table.style.width || '100%';
            table.style.borderCollapse = 'collapse';

            Array.from(table.rows).forEach(function(row, rowIndex) {
                Array.from(row.cells).forEach(function(cell) {
                    const isHeader =
                        cell.tagName.toLowerCase() === 'th' || rowIndex === 0;

                    cell.style.backgroundColor = isHeader ? headerBg : cellBg;
                    cell.style.color = isHeader ? headerColor : cellColor;
                    cell.style.border = '1px solid #dce2eb';
                    cell.style.padding = '8px';
                    cell.style.textAlign = 'left';
                    cell.style.fontWeight = isHeader ? '700' : '400';
                });
            });

            syncContent();
            updatePreview();
        }

        function styleNewTableCell(cell, isHeader, table) {
            const headerBg =
                table.dataset.headerBackground ||
                document.getElementById('tableHeaderBackground').value;

            const headerColor =
                table.dataset.headerColor ||
                document.getElementById('tableHeaderColor').value;

            const cellBg =
                table.dataset.cellBackground ||
                document.getElementById('tableCellBackground').value;

            const cellColor =
                table.dataset.cellColor ||
                document.getElementById('tableCellColor').value;

            cell.style.backgroundColor = isHeader ? headerBg : cellBg;
            cell.style.color = isHeader ? headerColor : cellColor;
            cell.style.border = '1px solid #dce2eb';
            cell.style.padding = '8px';
            cell.style.textAlign = 'left';
            cell.style.fontWeight = isHeader ? '700' : '400';
        }

        function addTableRow() {
            const table = activeTable || getCurrentTable();

            if (!table) {
                alert('Click inside a table first.');
                return;
            }

            const columns = tableCellCount(table) || 1;
            const row = table.insertRow(-1);

            for (let i = 0; i < columns; i++) {
                const cell = document.createElement('td');

                cell.innerHTML = '&nbsp;';
                row.appendChild(cell);

                styleNewTableCell(cell, false, table);
            }

            activeTable = table;

            syncContent();
            updatePreview();
        }

        function removeTableRow() {
            const table = activeTable || getCurrentTable();

            if (!table) {
                alert('Click inside a table first.');
                return;
            }

            if (table.rows.length <= 1) {
                alert('A table must have at least one row. Use Delete Table to remove the whole table.');
                return;
            }

            const cell = getSelectedCell(table);
            const rowIndex = cell ? cell.parentElement.rowIndex : table.rows.length - 1;

            table.deleteRow(rowIndex);

            activeTable = table;

            syncContent();
            updatePreview();
        }

        function addTableColumn() {
            const table = activeTable || getCurrentTable();

            if (!table) {
                alert('Click inside a table first.');
                return;
            }

            Array.from(table.rows).forEach(function(row, rowIndex) {
                const isHeader = rowIndex === 0;
                const cell = document.createElement(isHeader ? 'th' : 'td');

                cell.innerHTML = isHeader ?
                    'Header ' + (row.cells.length + 1) :
                    '&nbsp;';

                row.appendChild(cell);

                styleNewTableCell(cell, isHeader, table);
            });

            activeTable = table;

            syncContent();
            updatePreview();
        }

        function removeTableColumn() {
            const table = activeTable || getCurrentTable();

            if (!table) {
                alert('Click inside a table first.');
                return;
            }

            const columns = tableCellCount(table);

            if (columns <= 1) {
                alert('A table must have at least one column. Use Delete Table to remove the whole table.');
                return;
            }

            const cell = getSelectedCell(table);
            const columnIndex = cell ? cell.cellIndex : columns - 1;

            Array.from(table.rows).forEach(function(row) {
                if (row.cells[columnIndex]) {
                    row.deleteCell(columnIndex);
                }
            });

            activeTable = table;

            syncContent();
            updatePreview();
        }

        function deleteTable() {
            const table = activeTable || getCurrentTable();

            if (!table) {
                alert('Click inside a table first.');
                return;
            }

            table.remove();

            activeTable = null;
            document.getElementById('editorTableTools').hidden = true;

            syncContent();
            updatePreview();
        }

        function toFileLike(item) {
            return {
                name: item.name,
                type: item.type || '',
                size: item.size || 0,
                __url: item.url
            };
        }

        function createExistingPreviewButton(file) {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'preview-file-button';
            button.textContent = 'Preview';
            button.setAttribute('aria-label', 'Preview ' + file.name);

            button.style.cssText = [
                'padding:7px 11px',
                'border:1px solid #dce2eb',
                'border-radius:7px',
                'background:#fff',
                'color:#17284f',
                'font-size:12px',
                'font-weight:600',
                'cursor:pointer'
            ].join(';');

            button.addEventListener('click', function() {
                openAttachmentPreview(file);
            });

            return button;
        }

        function renderExistingGroup(config) {
            const block = document.getElementById(config.blockId);
            const list = document.getElementById(config.listId);
            const inputs = document.getElementById(config.inputsId);

            list.replaceChildren();
            inputs.replaceChildren();

            block.hidden = config.items.length === 0;

            if (!config.items.length) return;

            let kept = 0;

            config.items.forEach(function(item) {
                const removed = config.removed.has(item.id);
                const file = toFileLike(item);

                if (!removed) kept++;

                const row = document.createElement('div');
                row.className = 'selected-file' + (removed ? ' is-removed' : '');

                if (fileKind(file) === 'image') {
                    const image = document.createElement('img');

                    image.className = 'file-preview-thumb';
                    image.src = item.url;
                    image.alt = item.name;
                    image.title = item.name;
                    image.loading = 'lazy';

                    row.appendChild(image);
                } else {
                    const icon = document.createElement('div');

                    icon.className = 'file-type-icon';
                    icon.textContent = fileExtension(file);

                    row.appendChild(icon);
                }

                const info = document.createElement('div');
                info.className = 'selected-file-info';

                const name = document.createElement('strong');
                name.textContent = item.name;
                name.title = item.name;

                const meta = document.createElement('span');
                meta.textContent = (item.type || 'Unknown file type') + ' · ' + formatFileSize(file.size);

                if (removed) {
                    const badge = document.createElement('span');
                    badge.className = 'removed-badge';
                    badge.textContent = ' · Will be removed on save';
                    meta.appendChild(badge);
                }

                info.append(name, meta);

                const actions = document.createElement('div');

                actions.style.cssText = [
                    'display:flex',
                    'align-items:center',
                    'gap:8px',
                    'flex-shrink:0'
                ].join(';');

                const toggle = document.createElement('button');

                toggle.type = 'button';
                toggle.className = 'remove-file-button';
                toggle.textContent = removed ? 'Undo' : 'Remove';

                toggle.addEventListener('click', function() {
                    if (config.removed.has(item.id)) {
                        config.removed.delete(item.id);
                    } else {
                        config.removed.add(item.id);

                        const currentPreview = document.getElementById('attachmentPreviewModal');

                        if (currentPreview && attachmentPreviewOpen && activeAttachmentUrl === item.url) {
                            closeAttachmentPreview();
                        }
                    }

                    renderExistingFiles();
                    updateReaderAttachments();
                });

                actions.append(createExistingPreviewButton(file), toggle);
                row.append(info, actions);
                list.appendChild(row);

                if (removed) {
                    const input = document.createElement('input');

                    input.type = 'hidden';
                    input.name = config.inputName;
                    input.value = item.id;

                    inputs.appendChild(input);
                }
            });

            document.getElementById(config.countId).textContent =
                kept + ' of ' + config.items.length + ' kept';
        }

        function renderExistingFiles() {
            renderExistingGroup({
                items: existingAttachments,
                removed: removedAttachmentIds,
                blockId: 'existingAttachmentsBlock',
                listId: 'existingAttachmentsList',
                countId: 'existingAttachmentsCount',
                inputsId: 'removeAttachmentInputs',
                inputName: 'remove_attachments[]'
            });

            renderExistingGroup({
                items: existingMedia,
                removed: removedMediaIds,
                blockId: 'existingMediaBlock',
                listId: 'existingMediaList',
                countId: 'existingMediaCount',
                inputsId: 'removeMediaInputs',
                inputName: 'remove_media[]'
            });
        }

        function updateReaderAttachments() {
            const section = document.getElementById('readerAttachmentsSection');
            const list = document.getElementById('readerAttachmentsList');

            const keptExisting = existingAttachments
                .filter(function(item) {
                    return !removedAttachmentIds.has(item.id);
                })
                .map(toFileLike);

            const allFiles = keptExisting.concat(attachmentFiles);

            list.replaceChildren();
            section.hidden = allFiles.length === 0;

            allFiles.forEach(function(file) {
                const row = document.createElement('div');
                row.className = 'reader-attachment';

                const icon = document.createElement('div');
                icon.className = 'reader-attachment-icon';
                icon.textContent = fileExtension(file);

                const name = document.createElement('div');
                name.className = 'reader-attachment-name';
                name.textContent = file.name + ' · ' + formatFileSize(file.size);

                row.append(icon, name);
                list.appendChild(row);
            });
        }

        function openPreview() {
            syncContent();
            updatePreview();
            updateReaderAttachments();

            modal.classList.add('open');
            modal.setAttribute('aria-hidden', 'false');

            document.body.style.overflow = 'hidden';

            previewOpen = true;
        }

        function closePreview() {
            modal.classList.remove('open');
            modal.setAttribute('aria-hidden', 'true');

            previewOpen = false;

            if (!attachmentPreviewOpen) {
                document.body.style.overflow = '';
            }
        }

        document.getElementById('chooseThumbnail').addEventListener('click', function() {
            thumbnailInput.click();
        });

        document.getElementById('replaceThumbnail').addEventListener('click', function() {
            thumbnailInput.click();
        });

        document.getElementById('removeThumbnail').addEventListener('click', function() {
            const discardingReplacement = Boolean(
                thumbnailFile && existingThumbnail && !thumbnailRemoved
            );

            thumbnailInput.value = '';

            if (!discardingReplacement && existingThumbnail) {
                thumbnailRemoved = true;
            }

            updateThumbnailPreview(null);
            updatePreview();
        });

        document.getElementById('undoRemoveThumbnail').addEventListener('click', function() {
            thumbnailRemoved = false;

            updateThumbnailPreview(null);
            updatePreview();
        });

        thumbnailInput.addEventListener('change', function() {
            const file = thumbnailInput.files[0];

            if (!file) return;

            if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                alert('Please select a JPG, PNG, or WebP image.');
                thumbnailInput.value = '';
                return;
            }

            if (file.size > MAX_THUMBNAIL_SIZE) {
                alert('The thumbnail must not exceed 4 MB.');
                thumbnailInput.value = '';
                return;
            }

            updateThumbnailPreview(file);
            updatePreview();
        });

        thumbnailFit.addEventListener('change', applyThumbnailSettings);
        thumbnailPosition.addEventListener('change', applyThumbnailSettings);
        thumbnailBackgroundStyle.addEventListener('change', applyBackgroundStyle);
        thumbnailBackground.addEventListener('input', applyBackgroundStyle);
        thumbnailGradientStart.addEventListener('input', applyBackgroundStyle);
        thumbnailGradientEnd.addEventListener('input', applyBackgroundStyle);

        document.getElementById('chooseAttachments').addEventListener('click', function() {
            attachmentInput.click();
        });

        attachmentInput.addEventListener('change', function() {
            // addAttachments() re-fills this input through syncAttachmentInput().
            // The input must keep that list, so it is not cleared afterwards;
            // clearing it would send an empty attachment_files[] on submit.
            const pickedFiles = Array.from(attachmentInput.files);

            addAttachments(pickedFiles);
        });

        const attachmentDropzone = document.getElementById('attachmentDropzone');

        attachmentDropzone.addEventListener('dragover', function(event) {
            event.preventDefault();
            attachmentDropzone.classList.add('drag-over');
        });

        attachmentDropzone.addEventListener('dragleave', function() {
            attachmentDropzone.classList.remove('drag-over');
        });

        attachmentDropzone.addEventListener('drop', function(event) {
            event.preventDefault();
            attachmentDropzone.classList.remove('drag-over');

            if (event.dataTransfer && event.dataTransfer.files.length) {
                addAttachments(event.dataTransfer.files);
            }
        });

        document.getElementById('addTagButton').addEventListener('click', function() {
            addTag(tagSearch.value);
        });

        tagSearch.addEventListener('keydown', function(event) {
            if (event.key === 'Enter' || event.key === ',') {
                event.preventDefault();
                addTag(tagSearch.value);
            }

            if (event.key === 'Backspace' && !tagSearch.value && selectedTags.length) {
                selectedTags.pop();

                updateTagHiddenField();
                renderSelectedTags();
                renderTagRecommendations();
                updatePreview();
            }
        });

        tagSearch.addEventListener('input', renderTagRecommendations);

        document.querySelectorAll('[data-command]').forEach(function(button) {
            button.addEventListener('mousedown', saveEditorSelection);

            button.addEventListener('click', function() {
                execEditorCommand(button.dataset.command);
            });
        });

        document.getElementById('editorBlock').addEventListener('change', function() {
            execEditorCommand('formatBlock', this.value);
        });

        document.getElementById('alignLeft').addEventListener('click', function() {
            execEditorCommand('justifyLeft');
        });

        document.getElementById('alignCenter').addEventListener('click', function() {
            execEditorCommand('justifyCenter');
        });

        document.getElementById('alignRight').addEventListener('click', function() {
            execEditorCommand('justifyRight');
        });

        document.getElementById('insertLinkButton').addEventListener('mousedown', saveEditorSelection);

        document.getElementById('insertLinkButton').addEventListener('click', function() {
            const url = prompt('Enter the link URL, including https://');

            if (!url) return;

            if (!/^https?:\/\//i.test(url)) {
                alert('Please enter a URL starting with http:// or https://.');
                return;
            }

            execEditorCommand('createLink', url);
        });

        document.getElementById('insertImageButton').addEventListener('mousedown', saveEditorSelection);

        document.getElementById('insertImageButton').addEventListener('click', function() {
            saveEditorSelection();
            editorImageInput.click();
        });

        editorImageInput.addEventListener('change', function() {
            const file = editorImageInput.files[0];

            if (!file) return;

            if (!file.type.startsWith('image/')) {
                alert('Please choose an image file.');
                editorImageInput.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function() {
                const image = document.createElement('img');

                image.src = reader.result;
                image.alt = file.name;
                image.style.maxWidth = '100%';

                restoreEditorSelection();

                const selection = window.getSelection();

                if (selection && selection.rangeCount && editor.contains(selection.anchorNode)) {
                    const range = selection.getRangeAt(0);

                    range.deleteContents();
                    range.insertNode(image);
                    range.setStartAfter(image);
                    range.collapse(true);

                    selection.removeAllRanges();
                    selection.addRange(range);
                } else {
                    editor.appendChild(image);
                }

                saveEditorSelection();
                syncContent();
                updatePreview();

                editorImageInput.value = '';
            };

            reader.readAsDataURL(file);
        });

        editor.addEventListener('paste', function(event) {
            const items = event.clipboardData && event.clipboardData.items ?
                Array.from(event.clipboardData.items) : [];

            const imageItem = items.find(function(item) {
                return item.type && item.type.startsWith('image/');
            });

            if (!imageItem) return;

            const file = imageItem.getAsFile();

            if (!file) return;

            event.preventDefault();

            const reader = new FileReader();

            reader.onload = function() {
                insertHtmlAtCursor(
                    '<p><img src="' + reader.result + '" alt="Pasted image" style="max-width:100%;height:auto"></p>'
                );
            };

            reader.readAsDataURL(file);
        });

        editor.addEventListener('drop', function(event) {
            const files = Array.from(event.dataTransfer.files || []);

            const image = files.find(function(file) {
                return file.type.startsWith('image/');
            });

            if (!image) return;

            event.preventDefault();

            const reader = new FileReader();

            reader.onload = function() {
                insertHtmlAtCursor(
                    '<p><img src="' + reader.result + '" alt="' +
                    escapeHtml(image.name) +
                    '" style="max-width:100%;height:auto"></p>'
                );
            };

            reader.readAsDataURL(image);
        });

        document.getElementById('insertTableButton').addEventListener('mousedown', saveEditorSelection);

        document.getElementById('insertTableButton').addEventListener('click', function() {
            const rowsValue = prompt('How many rows? (1–20)', '3');

            if (rowsValue === null) return;

            const columnsValue = prompt('How many columns? (1–10)', '3');

            if (columnsValue === null) return;

            const rows = Number.parseInt(rowsValue, 10);
            const columns = Number.parseInt(columnsValue, 10);

            if (
                !Number.isInteger(rows) ||
                !Number.isInteger(columns) ||
                rows < 1 ||
                rows > 20 ||
                columns < 1 ||
                columns > 10
            ) {
                alert('Enter 1–20 rows and 1–10 columns.');
                return;
            }

            const headerBackground = document.getElementById('tableHeaderBackground').value;
            const headerColor = document.getElementById('tableHeaderColor').value;
            const cellBackground = document.getElementById('tableCellBackground').value;
            const cellColor = document.getElementById('tableCellColor').value;

            let html =
                '<table style="width:100%;border-collapse:collapse" ' +
                'data-header-background="' + headerBackground + '" ' +
                'data-header-color="' + headerColor + '" ' +
                'data-cell-background="' + cellBackground + '" ' +
                'data-cell-color="' + cellColor + '">' +
                '<tbody>';

            for (let r = 0; r < rows; r++) {
                html += '<tr>';

                for (let c = 0; c < columns; c++) {
                    const isHeader = r === 0;
                    const tag = isHeader ? 'th' : 'td';
                    const bg = isHeader ? headerBackground : cellBackground;
                    const color = isHeader ? headerColor : cellColor;
                    const text = isHeader ? 'Header ' + (c + 1) : '&nbsp;';

                    html += '<' + tag +
                        ' style="background-color:' + bg +
                        ';color:' + color +
                        ';border:1px solid #dce2eb' +
                        ';padding:8px' +
                        ';text-align:left' +
                        ';font-weight:' + (isHeader ? '700' : '400') +
                        '">' + text + '</' + tag + '>';
                }

                html += '</tr>';
            }

            html += '</tbody></table><p><br></p>';

            insertHtmlAtCursor(html);
            updateTableTools();
        });

        document.getElementById('addTableRow').addEventListener('click', addTableRow);
        document.getElementById('removeTableRow').addEventListener('click', removeTableRow);
        document.getElementById('addTableColumn').addEventListener('click', addTableColumn);
        document.getElementById('removeTableColumn').addEventListener('click', removeTableColumn);
        document.getElementById('deleteTable').addEventListener('click', deleteTable);

        [
            'tableHeaderBackground',
            'tableHeaderColor',
            'tableCellBackground',
            'tableCellColor'
        ].forEach(function(id) {
            document.getElementById(id).addEventListener('input', function() {
                const table = activeTable || getCurrentTable();

                if (!table) return;

                applyTableStyles(table);
            });
        });

        document.getElementById('clearEditor').addEventListener('click', function() {
            if (!editor.innerHTML.trim()) return;

            if (!confirm('Clear all text, formatting, images, and tables in the content editor?')) {
                return;
            }

            editor.innerHTML = '';
            activeTable = null;
            editorSelection = null;

            document.getElementById('editorTableTools').hidden = true;

            syncContent();
            updatePreview();
        });

        document.getElementById('undoEditor').addEventListener('click', function() {
            execEditorCommand('undo');
        });

        document.getElementById('redoEditor').addEventListener('click', function() {
            execEditorCommand('redo');
        });

        editor.addEventListener('keyup', function() {
            saveEditorSelection();
            syncContent();
            updatePreview();
            updateTableTools();
        });

        editor.addEventListener('mouseup', function() {
            saveEditorSelection();
            updateTableTools();
        });

        editor.addEventListener('input', function() {
            syncContent();
            updatePreview();
            updateTableTools();
        });

        editor.addEventListener('click', updateTableTools);

        editor.addEventListener('keydown', function(event) {
            if (event.key !== 'Tab') return;

            const table = getCurrentTable();

            if (!table) return;

            const selection = window.getSelection();
            let node = selection && selection.anchorNode;

            if (node && node.nodeType === Node.TEXT_NODE) {
                node = node.parentElement;
            }

            const cell = node && node.closest ?
                node.closest('td, th') :
                null;

            if (!cell) return;

            event.preventDefault();

            const cells = Array.from(table.querySelectorAll('td, th'));
            const index = cells.indexOf(cell);
            const next = cells[index + (event.shiftKey ? -1 : 1)];

            if (next) {
                const range = document.createRange();

                range.selectNodeContents(next);
                range.collapse(false);

                selection.removeAllRanges();
                selection.addRange(range);
            } else if (!event.shiftKey) {
                addTableRow();

                const lastCell = table.querySelector(
                    'tr:last-child td:last-child, tr:last-child th:last-child'
                );

                if (lastCell) {
                    const range = document.createRange();

                    range.selectNodeContents(lastCell);
                    range.collapse(false);

                    selection.removeAllRanges();
                    selection.addRange(range);
                }
            }

            saveEditorSelection();
            syncContent();
        });

        [title, summary, scheduledAt, expiresAt].forEach(function(input) {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        });

        title.addEventListener('input', function() {
            updateCount(title, 'titleCount', 150);
        });

        summary.addEventListener('input', function() {
            updateCount(summary, 'summaryCount');
        });

        status.addEventListener('change', updateStatusFields);
        pinned.addEventListener('change', updatePinSettings);

        document.getElementById('openPreviewTop').addEventListener('click', openPreview);
        document.getElementById('openPreviewBottom').addEventListener('click', openPreview);
        document.getElementById('openPreviewSide').addEventListener('click', openPreview);
        document.getElementById('closePreview').addEventListener('click', closePreview);

        document.querySelectorAll('[data-close-preview]').forEach(function(element) {
            element.addEventListener('click', closePreview);
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                if (attachmentPreviewOpen) {
                    closeAttachmentPreview();
                } else if (previewOpen) {
                    closePreview();
                }
            }
        });

        form.addEventListener('submit', function(event) {
            syncContent();
            updateTagHiddenField();
            syncAttachmentInput();

            if (!editor.innerText.trim()) {
                event.preventDefault();

                alert('Please enter the announcement content before saving.');

                editor.focus();
                return;
            }

            if (status.value === 'scheduled' && !scheduledAt.value) {
                event.preventDefault();

                alert('Please select the scheduled publication date and time.');

                scheduledAt.focus();
                return;
            }

            if (
                expiresAt.value &&
                (status.value === 'scheduled' || (status.value === 'published' && publishedAtInitial))
            ) {
                const startValue = status.value === 'scheduled' ?
                    scheduledAt.value :
                    publishedAtInitial;

                if (startValue && expiresAt.value <= startValue) {
                    event.preventDefault();

                    alert('The expiration time must be later than the publication time.');

                    expiresAt.focus();
                    return;
                }
            }

            const totalAttachmentSize = attachmentFiles.reduce(function(total, file) {
                return total + file.size;
            }, 0);

            if (totalAttachmentSize > MAX_ATTACHMENT_SIZE) {
                event.preventDefault();

                alert('The combined attachment size must not exceed 100 MB.');
            }
        });

        if (initialContent.value.trim()) {
            editor.innerHTML = initialContent.value;
        }

        const initialCategory = (category.value || '')
            .split(',')
            .map(function(tag) {
                return tag.trim();
            })
            .filter(Boolean);

        selectedTags = initialCategory.filter(function(tag, index, all) {
            return all.findIndex(function(item) {
                return item.toLowerCase() === tag.toLowerCase();
            }) === index;
        });

        try {
            tagSuggestions = @json($categorySuggestions ?? []);

            if (!Array.isArray(tagSuggestions)) {
                tagSuggestions = [];
            }
        } catch (error) {
            tagSuggestions = [];
        }

        renderSelectedTags();
        renderTagRecommendations();

        updateCount(title, 'titleCount', 150);
        updateCount(summary, 'summaryCount');

        syncContent();
        updateStatusFields();
        updatePinSettings();
        updatePreview();
        renderAttachments();
        renderExistingFiles();
        updateReaderAttachments();
        updateThumbnailPreview(null);
        applyThumbnailSettings();
        updatePreview();
    });
</script>

@endsection