const editor = window.wp.data.dispatch('core/editor')
const savePost = editor.savePost

editor.savePost = function () {
    return savePost()
        .then(() => {
          location.reload();
        })
}