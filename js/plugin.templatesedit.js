if (!window.jQuery) alert("jQuery  не подключен. \\nДля включения библиотеки jQuery, в настройках плагина templatesEdit установите Load JQuery - true");
window.j = jQuery.noConflict(true);
var tplEdit = {
    pitems: '.pane-items',
    pitem: '.pane-item',
    psplit: '.pane-split .pane-item-split',
    tabs: '.tab-row',
    tab: '.tab-row h2',
    pages: '.tab-pages',
    page: '.tab-page',
    warning: '.warning:first',
    comment: '.comment:first',
    templateid: j('[name=template]').val(),
    manager_theme: manager_theme,
    init: function () {
        var _self = this;
        // исправляем шаблон D3X
        ////////////////////////////////
        if (_self.manager_theme == 'D3X' || _self.manager_theme == 'D3X_commerce') {
            j('#documentPane').before('<style>.dynamic-tab-pane-control .tab-row{background-position:0 100%;background-repeat:repeat-x;height: auto;margin-bottom: 2px;}.dynamic-tab-pane-control .tab-row .tab{height:auto;background-color: #EFF4F5 !important;padding: 3px 15px;}.dynamic-tab-pane-control .tab-row .tab.selected{height:auto;background-color: #E9F1F3 !important;}</style>');
        }
        /////////////////////////////////
        j(_self.pitem + ':not(.pane-item-split)').append(_self.boxItemEdit);
        j(_self.page + ' .pane-item-split').prepend('<div class="pane-item-drag" title="Переместить"></div>');
        j(_self.tab + '[data-visibility=hidden]').addClass('tab-hidden');
        j(_self.page).each(function (i, el) {
            j(_self.tab).eq(i).attr('data-tabindex', '#' + j(el).attr('id'));
        });
        j(_self.tab).append(_self.boxTabEdit);
        j(_self.page).wrapAll('<div class="tab-pages">');
        j(_self.page).prepend('<div class="pane-split">' + _self.splitItem.replace('%id%', _self.newID()) + '</div>');
        j('.pane-item-title .warning, .pane-item-title .comment, .pane-item-helparea, .tab-row h2.tab span:not(' + _self.tab + '[data-tabindex=#tabMeta] span,' + _self.tab + '[data-tabindex=#tabAccess] span)').attr('contenteditable', true);
        j(_self.tab + '[data-tabindex=#tabMeta] .tab-edit-box,' + _self.tab + '[data-tabindex=#tabAccess] .tab-edit-box').css('visibility', 'hidden');
        j('#actions .actionButtons li').remove();
        j('#actions .actionButtons').append('<li id="Button1"><a href="#" class="primary"><img alt="icons_save" src="media/style/' + _self.manager_theme + '/images/icons/save.png"> Сохранить шаблон</a></li><li id="Button2"><a href="#" onclick="location.reload();"><img src="media/style/' + _self.manager_theme + '/images/icons/refresh.png"> Обновить страницу</a></li><li id="Button3"><a href="#"><img src="media/style/' + _self.manager_theme + '/images/icons/information.png"> Сбросить шаблон по умолчанию</a></li>');
        j('#Button1 a').click(function () {
            _self.setData()
        });
        j('#Button3 a').click(function () {
            _self.setDefaultTemplate()
        });
        _self.sortItems();
        _self.sortTabs();
        _self.dropTabs();
        j(_self.tab + '[data-tabindex=#tabMeta],' + _self.tab + '[data-tabindex=#tabAccess]').droppable({
            disabled: true
        });
        _self.tabEdit();
        _self.itemHover();
    },
    splitItem: '<div class="pane-item pane-item-split" data-item-name="split_%id%"><div class="pane-item-drag" title="Переместить"></div><div class="pane-item-title"><span class="warning" contenteditable="true">Новая группа</span></div></div>',
    boxTabEdit: '<div class="tab-edit-box"><div class="tab-item-drag" title="Переместить"></div><div class="tab-item-visibility" title="Изменить видимость"></div><div class="tab-item-add" title="Добавить вкладку"></div><div class="tab-item-delete" title="Удалить вкладку"></div></div>',
    boxItemEdit: '<div class="pane-item-box-edit"><div class="pane-item-drag" title="Переместить"></div><div class="pane-item-edittitle" title="Добавить / удалить название"></div><div class="pane-item-visibility" title="Изменить видимость"></div><div class="pane-item-edithelp" title="Добавить / удалить подсказку"></div><div class="pane-item-helparea" contenteditable="true"></div></div>',
    newTab: '<h2 class="tab selected" data-tabindex=""><span contenteditable="true">Новая вкладка</span></h2>',
    newPage: '<div class="tab-page"><div class="pane-items"></div></div>',
    tabEdit: function () {
        var _self = this;
        j('.tab-edit-box div').unbind();
        j(_self.tab + '[data-tabindex=#tabGeneral] .tab-item-delete, ' + _self.tab + '[data-tabindex=#tabGeneral] .tab-item-visibility').remove();
        j(_self.tab).click(function () {
            var _id = j(this).data('tabindex');
            j(_self.page).hide();
            j(_id).show();
            j(_self.tab).removeClass('selected').css({
                zIndex: 1
            });
            j(this).addClass('selected').css({
                zIndex: 999
            });
        });
        j('.tab-item-drag').click(function () {
            return false;
        });
        j('.tab-item-visibility').click(function () {
            var _id = j(this).closest('h2').attr('data-tabindex');
            if (j(_id).hasClass('pane-page-hidden') && j(_self.tab + '[data-tabindex=' + _id + ']').hasClass('tab-hidden')) {
                j(_id).css({
                    opacity: 1
                }).removeClass('pane-page-hidden');
                j(_self.tab + '[data-tabindex=' + _id + ']').css({
                    opacity: 1
                }).removeClass('tab-hidden')
            } else {
                j(_id).addClass('pane-page-hidden');
                j(_self.tab + '[data-tabindex=' + _id + ']').addClass('tab-hidden')
            }
            return false;
        });
        j('.tab-item-add').click(function () {
            var _id = j(this).closest('h2').attr('data-tabindex');
            _self.addNewTab(_id);
            return false;
        });
        j('.tab-item-delete').click(function () {
            var _id = j(this).closest('h2').attr('data-tabindex');
            confirmTxt = 'Вы действительно хотите удалить вкладку ' + j(_self.tab + '[data-tabindex=' + _id + ']').text() + '?';
            if (confirm(confirmTxt)) {
                var tmp_items = j(_self.pitem, j(_id));
                j(_self.tab + '[data-tabindex=' + _id + ']').remove();
                j(_id).remove();
                tmp_items.appendTo(j(_self.pitems, j('#tabGeneral')));
                j(_self.page).hide();
                j(_self.tab).removeClass('selected');
                j(_self.tab + '[data-tabindex=#tabGeneral]').addClass('selected');
                j('#tabGeneral').show()
            }
            return false;
        });
        _self.splitItems();
    },
    newID: function () {
        return new Date().getTime();
    },
    addNewTab: function (id) {
        var _self = this;
        var uniqid = _self.newID();
        j(_self.page).hide();
        j(_self.tab).removeClass('selected');
        j(_self.tab + '[data-tabindex=' + id + ']').after(_self.newTab);
        j(id).after(_self.newPage);
        j(_self.tab + '[data-tabindex=' + id + ']').next().attr('data-tabindex', '#tab' + uniqid);
        j(id).next().attr('id', 'tab' + uniqid);
        j(_self.tab + '[data-tabindex=#tab' + uniqid + ']').append(_self.boxTabEdit);
        j(_self.tab + '[data-tabindex=#tab' + uniqid + '] span').focus();
        j('#tab' + uniqid + _self.page).prepend('<div class="pane-split">' + _self.splitItem.replace('%id%', _self.newID()) + '</div>');
        _self.tabEdit();
        _self.dropTabs();
    },
    itemHover: function () {
        var _self = this;
        j(_self.pitem).find('.pane-item-box-edit div').unbind();
        j(_self.pitem).hover(function () {
            j('.pane-item-helparea', this).text(j('img#item-help-' + j(this).data('item-name'), this).attr('alt'));
        });
        j('.pane-item-visibility').click(function () {
            var _item = j(this).closest(_self.pitem);
            if (_item.hasClass('pane-item-hidden')) {
                _item.removeClass('pane-item-hidden');
            } else {
                _item.addClass('pane-item-hidden');
            }
        });
        j('.pane-item-edittitle').click(function () {
            var _item = j(this).closest(_self.pitem);
            var ptitle = _item.find('.pane-item-title');
            var confirmTxt = 'Вы действительно хотите удалить название ?';
            if (ptitle.length) {
                if (ptitle.text() != '') {
                    if (confirm(confirmTxt)) {
                        ptitle.remove();
                        _item.find('.pane-item-f').addClass('pane-item-w');
                    }
                } else {
                    ptitle.remove();
                    _item.find('.pane-item-f').addClass('pane-item-w');
                }
            } else {
                _item.prepend('<div class="pane-item-title"><span class="warning" contenteditable="true"></span><span class="comment" contenteditable="true"></span></div>');
                _item.find('.pane-item-f').removeClass('pane-item-w');
            }
        });
        j('.pane-item-edithelp').click(function () {
            var _item = j(this).closest(_self.pitem);
            var phelp = j('img#item-help-' + _item.data('item-name'));
            var phelp_area = _item.find('.pane-item-helparea');
            var confirmTxt = 'Вы действительно хотите удалить подсказку ?';
            if (phelp.length) {
                if (phelp_area.is(':visible')) {
                    if (phelp.attr('alt') != '') {
                        if (confirm(confirmTxt)) {
                            phelp_area.text('');
                            phelp.remove()
                        }
                    } else {
                        phelp_area.text('');
                        phelp.remove()
                    }
                } else {
                    phelp_area.text(phelp.attr('alt'));
                    phelp_area.keyup(function () {
                        phelp.attr('alt', j(this).text())
                    });
                }
            } else {
                _item.find('.pane-item-f').append(' <img id="item-help-' + _item.data('item-name') + '" src="media/style/' + _self.manager_theme + '/images/icons/b02_trans.gif" alt="' + phelp_area.text() + '" style="cursor:help;" class="tooltip">');
                phelp_area.keyup(function () {
                    j('img#item-help-' + _item.data('item-name')).attr('alt', j(this).text())
                });
            }
        });
    },
    splitItems: function () {
        var _self = this;
        j('.pane-split').sortable({
            start: function (event, ui) {
                ui.helper.parent().height(ui.placeholder.outerHeight())
            },
            update: function (event, ui) {
                j(this).append(_self.splitItem.replace('%id%', _self.newID()));
            },
            dropOnEmpty: false,
            axis: 'y',
            handle: '.pane-item-drag',
            connectWith: _self.pitems,
            zIndex: 999
        });
        j(_self.psplit).mousedown(function () {
            j('.pane-split').sortable('enable');
        });
        j(_self.psplit).mouseup(function (e) {
            setTimeout(function () {
                j('.pane-split').sortable('destroy');
                _self.splitItems();
            }, 200);
        });
        j('.pane-split').droppable({
            accept: _self.pitem,
            hoverClass: 'ui-drop-hover',
            drop: function (event, ui) {
                ui.draggable.remove()
            }
        })
    },
    sortItems: function () {
        var _self = this;
        j(_self.pitems).sortable({
            handle: '.pane-item-drag',
            zIndex: 999,
            start: function (event, ui) {
                if (!ui.item.hasClass('pane-item-split')) {
                    j(_self.psplit).parent().droppable('disable')
                }
            },
            stop: function (event, ui) {
                if (!ui.item.hasClass('pane-item-split')) {
                    j(_self.psplit).parent().droppable('enable')
                }
            }
        });
    },
    sortTabs: function () {
        var _self = this;
        j(_self.tabs).sortable({
            axis: 'x',
            delay: 100,
            opacity: 0.7,
            handle: 'div.tab-item-drag',
            items: 'h2:not([data-tabindex=#tabMeta], [data-tabindex=#tabAccess])',
            tolerance: 'pointer',
            cancel: 'span',
            update: function (event, ui) {
                var tmp_id;
                j('h2', this).each(function (i, el) {
                    var id = j(el).data('tabindex');
                    if (j(el).hasClass('selected')) {
                        tmp_id = id;
                    } else {
                        var tmp = j(id).clone();
                        if (tmp_id) {
                            j(id + ':first').remove();
                            j(tmp_id).after(tmp);
                            tmp_id = id;
                        } else {
                            j(id).remove();
                            j(_self.pages).prepend(tmp);
                            tmp_id = id;
                        }
                    }
                    _self.sortItems();
                });
                _self.itemHover();
            }
        });
    },
    dropTabs: function () {
        var _self = this;
        j(_self.tab).droppable({
            accept: _self.pitem + ':not(.pane-item-split)',
            tolerance: 'pointer',
            activeClass: 'ui-drop-highlight',
            hoverClass: "ui-drop-hover",
            forcePlaceholderSize: true,
            drop: function (event, ui) {
                var _list = j(j(this).data('tabindex') + ' ' + _self.pitems);
                ui.draggable.css({
                    visibility: 'hidden'
                }).hide(500, function () {
                    j(this).appendTo(_list).show(250).css({
                        visibility: 'visible'
                    });
                    if (j(this).parent().find(_self.pitem).length == 1) {
                        _self.sortItems()
                    }
                });
            }
        });
    },
    htmlentities: function (s) {
        var div = document.createElement('div');
        var text = document.createTextNode(s);
        div.appendChild(text);
        return div.innerHTML;
    },
    cleanText: function (str) {
        return str.replace(/"/g, "&quot;");
    },
    setData: function () {
        var _self = this;
        var tsArr = {};
        j('#preLoader').show();
        j(_self.tab).each(function (i, h) {
            var grpID = j(h).data('tabindex').replace('#tab', '');
            if (grpID == 'Access' || grpID == 'Meta') {
                return
            } else {
                tsArr[grpID] = {};
                tsArr[grpID]['fields'] = {};
                tsArr[grpID]['title'] = _self.cleanText(j(h).text());
                tsArr[grpID]['roles'] = '';
                tsArr[grpID]['hide'] = j('#tab' + grpID).hasClass('pane-page-hidden') ? '1' : '0';
                j(_self.pitem, j('#tab' + grpID + ' ' + _self.pitems)).each(function () {
                    var field_type;
                    if (this.hasClass('pane-item-field')) {
                        field_type = 'field';
                    } else if (this.hasClass('pane-item-tv')) {
                        field_type = 'tv';
                    } else if (this.hasClass('pane-item-split')) {
                        field_type = 'split';
                    } else {
                        return
                    }
                    var item_id = j(this).data('item-name');
                    var title = j(_self.warning, this).text();
                    if (j(_self.warning, this).text() != '') {
                        if (j(_self.comment, this).text() != '') {
                            title = title + '||||' + j(_self.comment, this).text();
                        }
                    } else {
                        title = '';
                    }
                    var help = j('#item-help-' + item_id, this).length ? _self.cleanText(j('#item-help-' + item_id, this).attr('alt')) : '';
                    tsArr[grpID]['fields'][item_id] = {};
                    tsArr[grpID]['fields'][item_id][field_type] = {};
                    tsArr[grpID]['fields'][item_id][field_type]['title'] = _self.cleanText(title);
                    tsArr[grpID]['fields'][item_id][field_type]['help'] = help;
                    tsArr[grpID]['fields'][item_id][field_type]['name'] = item_id;
                    tsArr[grpID]['fields'][item_id][field_type]['roles'] = '';
                    tsArr[grpID]['fields'][item_id][field_type]['hide'] = this.hasClass('pane-item-hidden') ? '1' : '0';
                })
            }
        });
        var stringArr = JSON.stringify(tsArr);
        j.ajax({
            type: "POST",
            cache: false,
            url: window.location.protocol + '//' + window.location.host + '/' + 'assets/modules/templatesEdit/ajax-action.php',
            data: {
                templateid: _self.templateid,
                data: _self.htmlentities(stringArr)
            },
            success: function () {
                location.reload();
            },
            error: function () {
                alert('Ошибка сохранения шаблона');
            }
        });
    },
    setDefaultTemplate: function () {
        var _self = this;
        var confirmTxt = 'Вы действительно хотите сбросить шаблон по умолчанию ?';
        if (confirm(confirmTxt)) {
            j('#preLoader').show();
            j.ajax({
                type: "POST",
                cache: false,
                url: window.location.protocol + '//' + window.location.host + '/' + 'assets/modules/templatesEdit/ajax-action.php',
                data: {
                    templateid: _self.templateid,
                    reset: 'yes'
                },
                success: function () {
                    location.reload();
                },
                error: function () {
                    alert('Ошибка сброса шаблона');
                }
            });
        }
    }
}
j(document).ready(function () {
    tplEdit.init();
});
