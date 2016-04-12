/**
 * Plugin Name: TinyMCE Annotate
 * Description: Create annotations on your posts or pages
 * Version:     1.1.2
 * Author:      xyulex
 * Author URI:  https://profiles.wordpress.org/xyulex/
 * License:     GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

(function($) {
    tinymce.PluginManager.add('tma_annotate', function(editor, url) {

        var state;

        function tma_hide_action() {
            state = !state;
            editor.fire('tma_annotatehide', {
                state: state
            });
            body = editor.getBody();

            if (state) { // Hide
                current = editor.getContent();
                tinymce.each( editor.$('span.annotation'), function(node) {
                  editor.dom.remove(node, true);
                });
            } else { // Show
                if (!body) {
                    $(body).html('');
                }else {
                    $(body).html(current);
                }
            }
        }

        function tma_toggleHide() {
            var self = this;
            editor.on('tma_annotatehide', function(e) {
                self.active(e.state);
            });
        }

        editor.addCommand('tma_cmd_hide', tma_hide_action);

        // Create annotation
        editor.addButton('tma_annotate', {
            title: TMA.tooltips.annotation_create,
            image: url + '/img/annotation.png',
            onclick: function() {
                annotation = '';
                color = '#F0E465';
                node = editor.selection.getNode();
                nodeName = node.nodeName;

                if (nodeName == 'SPAN') {
                    nodeDataAnnotation = $(node).attr("data-annotation");
                    nodeDataStyle = $(node).css("background-color");

                    // Retrieve annotation and color
                    if (nodeDataAnnotation) {
                        annotation = nodeDataAnnotation;
                        var ctx = document.createElement('canvas').getContext('2d');
                        ctx.strokeStyle = nodeDataStyle;
                        var color = ctx.strokeStyle;
                    }
                }

                var selectedText = editor.selection.getContent();
                var selectedTextLength = selectedText.length;

                if (selectedTextLength > 0 || node.className == 'annotation') {
                    if (node.className == 'annotation') {
                        selectedText = node.innerHTML;
                    }
                    editor.windowManager.open({
                        title: TMA.tooltips.annotation_settings,
                        body: [{
                            type: 'textbox',
                            name: 'annotation',
                            label: TMA.settings.setting_annotation,
                            value: annotation
                        }, {
                            type: 'colorpicker',
                            name: 'annotationbg',
                            label: TMA.settings.setting_background,
                            value: color
                        }],

                        onsubmit: function(e) {
                            if (e.data.annotation) {
                                var dataAnnotation = e.data.annotation;

                                if ($(node).attr("data-annotation")) {
                                    editor.dom.remove(node);
                                }
                               editor.selection.setContent('<span class="annotation" data-author="' + TMA.author + '" data-annotation="' + dataAnnotation.replace(/"/g,'&quot;') + '" style="background-color:' + e.data.annotationbg + '">' + selectedText + '</span>');

                            } else {
                                editor.windowManager.alert(TMA.errors.missing_fields);
                                return false;
                            }
                        }
                    });
                } else {
                    editor.windowManager.alert(TMA.errors.missing_annotation, false);
                }
            }
        });

        // Delete annotation
        editor.addButton('tma_annotatedelete', {
            title: TMA.tooltips.annotation_delete,
            image: url + '/img/annotation-delete.png',
            onclick: function() {
                var selectedText = editor.selection.getContent();
                var selectedTextLength = selectedText.length;
                var node = editor.selection.getNode();
                if (selectedTextLength > 0 || node.className == 'annotation') {
                     if (node.className == 'annotation') {
                        selectedText = node.innerHTML;
                    }
                    deletionNode = editor.selection.getNode();
                    replaceNode = deletionNode;
                    $(deletionNode).attr("style", "");
                    editor.dom.remove(replaceNode, deletionNode);
                } else {
                    editor.windowManager.alert(TMA.errors.missing_selected);
                }
            }
        });

        // Hide all annotations
        editor.addButton('tma_annotatehide', {
            title: TMA.tooltips.annotation_hide,
            image: url + '/img/annotation-hide.png',
            cmd: 'tma_cmd_hide',
            onPostRender: tma_toggleHide
        });

    });
})(jQuery);