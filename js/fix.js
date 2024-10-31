(function(undefined) {
    function getWin() {
        // Added frameElement check to fix bug: #2817583
        return (!window.frameElement && window.dialogArguments) || opener || parent || top;
    }

    var mapping = {
        'tinymce.plugins.dom.DOMUtils': 'tinymce.dom.DOMUtils',

        'tinymce.plugins.util.Dispatcher': 'tinymce.util.Dispatcher'
    };

    var parentWin = getWin();

    if (!parentWin || !parentWin.tinymce)
        return ;

    var editor = parentWin.tinymce.EditorManager.activeEditor,
        createInstanceOriginal = editor.windowManager.createInstance;

    editor.windowManager.createInstance = function() {
        if (arguments.length > 0 && mapping[arguments[0]] !== undefined) {
            arguments[0] = mapping[arguments[0]];
        }

        return createInstanceOriginal.apply(this, arguments);
    };
})();