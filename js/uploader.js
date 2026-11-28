/**
 * Copyright 2026 The Horde Project (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (GPL). If you
 * did not receive this file, see http://www.horde.org/licenses/gpl.
 *
 * @author Michael J Rubinsky <mrubinsk@horde.org>
 * @author Ralf Lang <ralf.lang@ralf-lang.de>
 * @author Torben Dannhauer <torben@dannhauer.de>
 * @category Horde
 * @license http://www.horde.org/licenses/gpl GPL
 * @package Ansel
 */

/**
 * Vanilla JS file upload widget for Ansel.
 *
 * Replaces the former plupload-based Horde_Uploader.
 * Based on native HTML5 File API.
 * Supports drag-and-drop and fetch()/FormData to upload images to the gallery one file
 * at a time. Each file is sent with a POST request as
 * multipart/form-data to the configured target with a "name" query parameter
 * and a "file" form field. The server replies with a small JSON document
 * { "status": "200", "error": {} }.
 *
 * Usage:
 * ======
 *   var uploader = new AnselUploader({
 *       container:     'anseluploader',   // DOM id of the parent container
 *       target:        '/ansel/img/upload.php?gallery=1',
 *       return_target: '/ansel/view.php?...',
 *       text: {
 *           add:          'Add Images',
 *           start:        'Upload',
 *           header:       'Upload to gallery',
 *           subheader:    'Add files to the upload queue and click start.',
 *           returnButton: 'View Gallery',
 *           size:         'File size error.',
 *           type:         'File type error.'
 *       }
 *   }, {
 *       uploadcomplete: function(files) { ... }
 *   });
 *   uploader.init();
 *
 * Based on the original uploader by Michael J Rubinsky <mrubinsk@horde.org>
 */

var AnselUploader = (function () {
    'use strict';

    function AnselUploader(params, handlers) {
        this.params = Object.assign({
            browse_button: 'browseimages',
            upload_button: 'uploadimages',
            return_button: 'return',
            drop_target: 'filelist',
            browsebutton_class: 'horde-button',
            uploadbutton_class: 'horde-button',
            returnbutton_class: 'horde-button',
            container_class: 'hordeUploaderContainer',
            header_class: 'hordeUploaderHeader',
            headercontent_class: 'hordeUploaderHeaderContent',
            subheader_class: 'hordeUploaderSubHeader',
            filelist_class: 'hordeUploaderFilelist',
            filelistitem_class: 'hordeUploaderFilelistItem',
            filelistitemdone_class: 'hordeUploaderFilelistItemDone',
            filelistitemerror_class: 'hordeUploaderFilelistItemError',
            success_class: 'hordeUploaderSuccess',
            error_class: 'hordeUploaderError',
            footer_class: 'hordeUploaderFooter',
            max_file_size: 0,
            text: {}
        }, params || {});

        this.handlers = handlers || {};

        // Files queued for upload, keyed by a generated id.
        this._queue = [];
        this._seq = 0;
        this._uploading = false;
    }

    AnselUploader.prototype = {

        /**
         * Build the widget and wire up the controls.
         */
        init: function () {
            this._build();

            var self = this,
                input = this._fileInput,
                browse = document.getElementById(this.params.browse_button),
                upload = document.getElementById(this.params.upload_button),
                drop = document.getElementById(this.params.drop_target);

            browse.addEventListener('click', function (e) {
                e.preventDefault();
                input.click();
            });

            input.addEventListener('change', function () {
                self._enqueue(input.files);
                // Allow selecting the same file name again later.
                input.value = '';
            });

            upload.addEventListener('click', function (e) {
                e.preventDefault();
                self._startUpload();
            });

            // Drag and drop onto the file list.
            ['dragenter', 'dragover'].forEach(function (name) {
                drop.addEventListener(name, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    drop.classList.add('hordeUploaderDragOver');
                });
            });
            ['dragleave', 'drop'].forEach(function (name) {
                drop.addEventListener(name, function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    drop.classList.remove('hordeUploaderDragOver');
                });
            });
            drop.addEventListener('drop', function (e) {
                if (e.dataTransfer && e.dataTransfer.files) {
                    self._enqueue(e.dataTransfer.files);
                }
            });

            this._setHidden(this.params.upload_button, true);
            this._setHidden(this.params.return_button, true);

            if (this.params.return_target) {
                document.getElementById(this.params.return_button)
                    .addEventListener('click', function () {
                        window.location = self.params.return_target;
                    });
            }
        },

        /**
         * Add a FileList to the queue and render list rows for each file.
         */
        _enqueue: function (fileList) {
            var files = Array.prototype.slice.call(fileList || []),
                self = this,
                ul = this._ul;

            files.forEach(function (file) {
                if (!self._isAccepted(file)) {
                    window.alert((self.params.text.type || 'File type error.') +
                        ' ' + file.name);
                    return;
                }

                if (self.params.max_file_size &&
                    file.size > self.params.max_file_size) {
                    window.alert((self.params.text.size || 'File size error.') +
                        ' ' + file.name);
                    return;
                }

                var id = 'ansel-upload-' + (++self._seq);
                self._queue.push({ id: id, file: file });

                var li = document.createElement('li');
                li.className = self.params.filelistitem_class;
                li.id = id;
                li.appendChild(self._cell('hordeUploaderFilename', file.name));

                var action = self._cell('hordeUploaderFileaction', '');
                var remove = document.createElement('div');
                remove.className = 'hordeUploaderRemove';
                remove.innerHTML = '&nbsp;';
                remove.addEventListener('click', function () {
                    self._remove(id);
                });
                action.appendChild(remove);
                li.appendChild(action);

                li.appendChild(self._cell('hordeUploaderFilestatus', ' '));
                li.appendChild(self._cell('hordeUploaderFilesize',
                    self._formatSize(file.size)));
                ul.appendChild(li);
            });

            this._setHidden(this.params.upload_button, this._queue.length === 0);
        },

        /**
         * Remove a queued but not yet uploaded file.
         */
        _remove: function (id) {
            this._dequeue(id);
            var li = document.getElementById(id);
            if (li) {
                li.parentNode.removeChild(li);
            }
            if (!this._queue.length) {
                this._setHidden(this.params.upload_button, true);
            }
        },

        /**
         * Upload every queued file sequentially.
         */
        _startUpload: function () {
            if (this._uploading || !this._queue.length) {
                return;
            }
            this._uploading = true;
            this._setHidden(this.params.upload_button, true);

            var self = this,
                queue = this._queue.slice(),
                uploaded = [];

            function next(index) {
                if (index >= queue.length) {
                    self._uploading = false;
                    self._setHidden(self.params.return_button, uploaded.length === 0);
                    self._setHidden(self.params.upload_button, self._queue.length === 0);
                    if (uploaded.length && typeof self.handlers.uploadcomplete === 'function') {
                        self.handlers.uploadcomplete(uploaded);
                    }
                    return;
                }

                var entry = queue[index];
                self._uploadOne(entry).then(function () {
                    uploaded.push(entry.file);
                }).catch(function () {
                    // Error already rendered against the row. Keep going.
                }).then(function () {
                    next(index + 1);
                });
            }

            next(0);
        },

        /**
         * Upload a single file via fetch()/FormData.
         *
         * @return Promise  Resolves on a 200 status or rejects.
         */
        _uploadOne: function (entry) {
            var self = this,
                file = entry.file,
                data = new FormData(),
                url = this.params.target +
                    (this.params.target.indexOf('?') === -1 ? '?' : '&') +
                    'name=' + encodeURIComponent(file.name);

            data.append('file', file, file.name);

            return fetch(url, {
                method: 'POST',
                body: data,
                credentials: 'same-origin'
            }).then(function (response) {
                return response.text();
            }).then(function (body) {
                var result = null;
                if (body) {
                    try {
                        result = JSON.parse(body);
                    } catch (e) {
                        result = null;
                    }
                }
                if (!result || parseInt(result.status, 10) !== 200) {
                    var message = (result && result.error && result.error.message) ||
                        (self.params.text.type || 'File type error.');
                    self._markError(entry.id, message);
                    throw new Error(message);
                }
                self._markDone(entry.id);
                return result;
            }).catch(function (err) {
                var li = document.getElementById(entry.id);
                if (!li || li.dataset.settled !== '1') {
                    self._markError(
                        entry.id,
                        self.params.text.type || 'File type error.'
                    );
                }
                return Promise.reject(err);
            });
        },

        /**
         * Accept images and zip archives. An empty or generic browser type
         * is left for the server to judge.
         */
        _isAccepted: function (file) {
            var type = (file.type || '').toLowerCase();
            if (!type || type === 'application/octet-stream') {
                return true;
            }
            if (type.indexOf('image/') === 0) {
                return true;
            }
            return type === 'application/zip' ||
                type === 'application/x-zip-compressed' ||
                type === 'application/x-compressed';
        },

        _dequeue: function (id) {
            this._queue = this._queue.filter(function (entry) {
                return entry.id !== id;
            });
        },

        _markDone: function (id) {
            var li = document.getElementById(id);
            this._dequeue(id);
            if (!li) {
                return;
            }
            li.dataset.settled = '1';
            li.classList.add(this.params.filelistitemdone_class);
            this._setStatus(id, ' ');
            this._dropRemove(li);
            this._markAction(li, this.params.success_class);
        },

        _markError: function (id, message) {
            var li = document.getElementById(id);
            this._dequeue(id);
            if (!li || li.dataset.settled === '1') {
                return;
            }
            li.dataset.settled = '1';
            li.classList.add(this.params.filelistitemerror_class);
            this._setStatus(id, message);
            this._dropRemove(li);
            this._markAction(li, this.params.error_class);
        },

        _markAction: function (li, cls) {
            var action = li.querySelector('.hordeUploaderFileaction');
            if (action && cls) {
                action.classList.add(cls);
            }
        },

        _dropRemove: function (li) {
            var remove = li.querySelector('.hordeUploaderRemove');
            if (remove) {
                remove.parentNode.removeChild(remove);
            }
        },

        _setStatus: function (id, text) {
            var li = document.getElementById(id);
            if (!li) {
                return;
            }
            var status = li.querySelector('.hordeUploaderFilestatus');
            if (status) {
                status.textContent = text;
            }
        },

        _cell: function (cls, text) {
            var div = document.createElement('div');
            div.className = cls;
            if (text === '') {
                div.innerHTML = '&nbsp;';
            } else {
                div.textContent = text;
            }
            return div;
        },

        _setHidden: function (id, hidden) {
            var el = document.getElementById(id);
            if (el) {
                el.style.display = hidden ? 'none' : '';
            }
        },

        _formatSize: function (bytes) {
            if (!bytes) {
                return '0 b';
            }
            var units = ['b', 'kb', 'mb', 'gb'],
                i = Math.floor(Math.log(bytes) / Math.log(1024));
            i = Math.min(i, units.length - 1);
            return (bytes / Math.pow(1024, i)).toFixed(i ? 1 : 0) + ' ' + units[i];
        },

        /**
         * Render the static widget into the container.
         */
        _build: function () {
            var p = this.params,
                text = p.text,
                container = document.getElementById(p.container);

            var headerContent = this._cell(p.headercontent_class, text.header || '');
            headerContent.appendChild(this._cell(p.subheader_class, text.subheader || ''));
            var header = document.createElement('div');
            header.className = p.header_class;
            header.appendChild(headerContent);

            var fileHeader = document.createElement('div');
            fileHeader.className = 'hordeUploaderFilelistHeader';
            fileHeader.appendChild(this._cell('hordeUploaderFilename', text.filename || 'Filename'));
            fileHeader.appendChild(this._cell('hordeUploaderFileaction', ''));
            fileHeader.appendChild(this._cell('hordeUploaderFilestatus', text.status || 'Status'));
            fileHeader.appendChild(this._cell('hordeUploaderFilesize', text.sizeLabel || 'Size'));

            var ul = document.createElement('ul');
            ul.className = 'hordeUploaderFileUl';
            var fileList = document.createElement('div');
            fileList.id = p.drop_target;
            fileList.className = p.filelist_class;
            fileList.appendChild(ul);
            this._ul = ul;

            var browse = this._button(p.browse_button, p.browsebutton_class, text.add);
            var upload = this._button(p.upload_button, p.uploadbutton_class, text.start);
            var ret = this._button(p.return_button, p.returnbutton_class, text.returnButton);

            var footer = document.createElement('div');
            footer.className = p.footer_class;
            footer.appendChild(browse);
            footer.appendChild(upload);
            footer.appendChild(ret);

            // Hidden native file input driving the browse button.
            var input = document.createElement('input');
            input.type = 'file';
            input.multiple = true;
            input.accept = 'image/*,.zip,application/zip,application/x-zip-compressed';
            input.style.display = 'none';
            this._fileInput = input;

            var widget = document.createElement('div');
            widget.className = p.container_class;
            widget.appendChild(header);
            widget.appendChild(fileHeader);
            widget.appendChild(fileList);
            widget.appendChild(footer);
            widget.appendChild(input);

            container.appendChild(widget);
        },

        _button: function (id, cls, value) {
            var input = document.createElement('input');
            input.type = 'button';
            input.id = id;
            input.className = cls;
            input.value = value || '';
            return input;
        }
    };

    return AnselUploader;
}());
