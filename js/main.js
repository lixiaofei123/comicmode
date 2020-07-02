function openComic(file,data){
    let path = data.dir + '/' + file
    window.location.href = OC.generateUrl('/apps/comicmode/read?dir=' + path)
}

function registerFileActions(){
    OCA.Files.fileActions.registerAction({
        name: t('comicmode', 'Read comic'),
        displayName: '',
        mime: 'dir',
        order: 0,
        permissions: OC.PERMISSION_ALL,
        type: OCA.Files.FileActions.TYPE_INLINE,
        icon: OC.generateUrl('/custom_apps/comicmode/img/app_black.svg'),
        actionHandler: openComic
    })
}

document.addEventListener('DOMContentLoaded', function () {
    if (typeof OCA !== 'undefined' && typeof OCA.Files !== 'undefined' && typeof OCA.Files.fileActions !== 'undefined' && $('#header').hasClass('share-file') === false) {
        registerFileActions();
    }
    return true;
});