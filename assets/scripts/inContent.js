function moveAddToParagraph(){
    var conteudoPost = document.querySelector('.conteudo_post');

    if (!conteudoPost) {
        return;
    }

    var paragrafosPost = conteudoPost.childNodes,
    inContent = document.querySelector('.in_content_banner');

    if (!inContent) {
        return;
    }


    paragrafosPost = [].filter.call( paragrafosPost, function (elem, i, arr) {
        return elem.nodeName === 'P';
    });

    if (paragrafosPost.length > 4) {
        inContent.remove();
        paragrafosPost[paragrafosPost.length - 2].prepend(inContent);
        inContent.querySelector('#gdfp_in-content').style.margin = '15px 0';
        inContent.querySelector('#gdfp_in-content').style.padding = '0';
        inContent.querySelector('#gdfp_in-content').style.display = 'block';
    }

    /**
     * googletag é a lib padrão do Google DFP, que é chamada no header das páginas.
     * gptAdSlots é o array de espaços com especificações de size mapping, também
     * chamados no header.
     * O header está views/gdfp_header.html
     * As linhas abaixo recarregam o anuncio apenas do espaço 'in-content', 
     * após o JS mudá-lo de lugar na página.
     */

    if (!googletag || !gptAdSlots) {
        return;
    }

    googletag.pubads().refresh(gptAdSlots[gptAdSlots.length - 1]);
}

document.addEventListener("DOMContentLoaded", function(event) {
    moveAddToParagraph();
});
