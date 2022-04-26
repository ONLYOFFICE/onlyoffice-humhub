/**
 *  Copyright (c) Ascensio System SIA 2022. All rights reserved.
 *  http://www.onlyoffice.com
 */

humhub.module('onlyoffice', function (module, require, $) {

    var client = require('client');
    var modal = require('ui.modal');
    var object = require('util').object;
    var Widget = require('ui.widget').Widget;
    var event = require('event');
    var loader = require('ui.loader');
    var ooJSLoadRetries = 0;

    var api = null;

    var Editor = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Editor, Widget);

    Editor.prototype.getDefaultOptions = function () {
        return {
            'fileName': 'unnamedFile.docx',
        };
    };

    Editor.prototype.init = function () {
        this.modal = modal.get('#onlyoffice-modal');

        if (this.options.moduleConfigured != 1) {
            module.log.error('No OnlyOffice server configured! - Check onlyoffice module configuration!', true);
            return
        }

        this.initEditor();

        var that = this;
        this.modal.$.on('hidden.bs.modal', function (evt) {
            that.modal.clear();
        });

    };

    Editor.prototype.share = function (evt) {
        m = modal.get('#onlyoffice-share-modal');
        m.load(evt.url);
        m.show();
    }

    Editor.prototype.convert = function (evt) {
        var that = this;
        $.ajax({
            type: "POST",
            url: that.options.downloadPost,
            success: function (url) {
                if (url == null) {
                    return;
                }
            }
        });
    }

    Editor.prototype.close = function (evt) {

        if (this.docEditor
            && this.options.editMode == 'edit') {
            if (this.docEditor.requestClose) {
                setTimeout(() => {
                    onRequestCloseObj = { that: this, evt: evt };
                    this.docEditor.requestClose();
                    evt.finish();
                }, 0);
            } else {
                refreshFileInfo(this, evt);
            }
        } else {
            closeModal(this, evt);
        }
    }

    Editor.prototype.initEditor = function () {
        if (!window.DocsAPI) {
            ooJSLoadRetries++;
            if (ooJSLoadRetries < 100) {
                setTimeout($.proxy(this.initEditor, this), 100);
                return;
            } else {
                module.log.error('Could not onlyoffice document editor.', true);
                return;
            }
        }

        api = this.options.api;

        var config = this.options.config;

        var docsVersion = DocsAPI.DocEditor.version().split(".");
        if (docsVersion[0] < 6
            || docsVersion[0] == 6 && docsVersion[1] == 0) {
            module.log.error('Not supported version', true);
            return;
        }
        if ((config.document.fileType === 'docxf' || config.document.fileType === 'oform')
            && docsVersion[0] < 7) {
            module.log.error('Please update ONLYOFFICE Docs to version 7.0 to work on fillable forms online', true);
            return;
        }

        config.width = "100%";
        config.height = "100%";
        config.events = {
            'onRequestClose': onRequestClose,
            //'onReady': onReady,
            //'onDocumentStateChange': onDocumentStateChange,
            //'onRequestEditRights': onRequestEditRights,
            //'onError': onError,
        };

        if (api.saveasUrl && location.search.indexOf('?r=cfiles') === 0) {
            config.events.onRequestSaveAs = onRequestSaveAs;
        }

        this.docEditor = new DocsAPI.DocEditor('iframeContainer', config);
    }

    var Convert = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Convert, Widget);

    Convert.prototype.init = function () {

        var that = this;
        var msg = that.$.find('#oConvertMessage');

        function _onError(error) {
            msg.text(that.options.errorMessage + ' ' + error);
            loader.reset(that.$.find('.modal-footer'));
        }

        function _callAjax() {
            jQuery.ajax({
                type: "POST",
                url: that.options.convertPost,
                cache: false,
                success: function (data) {
                    if (data.error) {
                        _onError(data.error);
                        return;
                    }

                    if (data.percent != null) {
                        msg.text(data.percent + "%");
                    }

                    if (!data.endConvert) {
                        setTimeout(_callAjax, 1000);
                    } else {
                        msg.text(that.options.doneMessage);
                        loader.reset(that.$.find('.modal-footer'));
                    }
                },
                error: _onError
            });
        }

        loader.set(that.$.find('.modal-footer'));
        _callAjax();
    };

    Convert.prototype.getDefaultOptions = function () {
        return {};
    };

    Convert.prototype.close = function (evt) {
        refreshFileInfo(this, evt);
    };

    function onRequestSaveAs(evt) {
        var saveData = {
            name: evt.data.title,
            url: evt.data.url
        };

        client.post(api.saveasUrl, {data: JSON.stringify(saveData), dataType: 'json'}).then((response) => {
            event.trigger('humhub:file:created.cfiles', [response.file]);
        }).catch(function(e) {
            module.log.error(e, true);
        });
    }

    var onRequestCloseObj = null;
    function onRequestClose() {
        refreshFileInfo(onRequestCloseObj.that, onRequestCloseObj.evt);
    };

    function refreshFileInfo(that, evt) {
        client.post({ url: that.options.fileInfoUrl }).then(function (response) {
            event.trigger('humhub:file:modified', [response.file]);
        }).catch(function (e) {
            module.log.error(e);
        }).finally(function () {
            closeModal(that, evt);
        });
    }

    function closeModal(that, evt) {
        if (that.docEditor) {
            that.docEditor.destroyEditor();
        }
        if (that.modal) {
            that.modal.clear();
            that.modal.close();
        }
        if (evt && evt.finish) {
            evt.finish();
        }
    }

    var Share = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Share, Widget);

    Share.prototype.init = function () {

        var that = this;

        if ($('.editLinkInput').find('input').val() != '') {
            $('.editLinkCheckbox').prop('checked', true);
            $(".editLinkCheckbox").attr('checked', true);
        } else {
            $('.editLinkInput').hide();
        }

        if ($('.viewLinkInput').find('input').val() != '') {
            $('.viewLinkCheckbox').attr('checked', true);
        } else {
            $('.viewLinkCheckbox').attr('checked', false);
            $('.viewLinkInput').hide();
        }

        $('.viewLinkCheckbox').change(function () {
            if ($('.viewLinkCheckbox:checked').length) {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareGetLink,
                    cache: false,
                    type: 'POST',
                    data: { 'shareMode': 'view' },
                    dataType: 'json',
                    success: function (json) {
                        $('.viewLinkInput').show();
                        $('.viewLinkInput').find('input').val(json.url)
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            } else {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareRemoveLink,
                    cache: false,
                    type: 'POST',
                    data: { 'shareMode': 'view' },
                    dataType: 'json',
                    success: function (jsoin) {
                        $('.viewLinkInput').hide();
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            }
        });

        $('.editLinkCheckbox').change(function () {
            if ($('.editLinkCheckbox:checked').length) {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareGetLink,
                    cache: false,
                    type: 'POST',
                    data: { 'shareMode': 'edit' },
                    dataType: 'json',
                    success: function (json) {
                        $('.editLinkInput').show();
                        $('.editLinkInput').find('input').val(json.url)
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            } else {
                loader.set(that.$.find('.modal-footer'));
                $.ajax({
                    url: that.options.shareRemoveLink,
                    cache: false,
                    type: 'POST',
                    data: { 'shareMode': 'edit' },
                    dataType: 'json',
                    success: function (jsoin) {
                        $('.editLinkInput').hide();
                        loader.reset(that.$.find('.modal-footer'));
                    }
                });
            }
        });

    };

    Share.prototype.getDefaultOptions = function () {
        return {};
    };

    Share.prototype.clickv = function (evt) {
        var that = this;
        //evt.$trigger
    }



    var init = function (pjax) { };

    var createSubmit = function (evt) {
        if ($('#cfiles-folderView').length > 0) {
            $('input#createdocument-fid').val($('#cfiles-folderView').attr('data-fid'));
        }

        client.submit(evt).then(function (response) {
            event.trigger('humhub:file:created', [response.file]);

            m = modal.get('#onlyoffice-modal');
            if (response.openFlag) {
                m.load(response.openUrl);
                m.show();
            } else {
                m.close();
            }

        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    module.export({
        init: init,
        createSubmit: createSubmit,
        Editor: Editor,
        Convert: Convert,
        Share: Share,
    });

});
