(function () {
    function findNestedLabel(node) {
        while (node && node !== document) {
            if (node.getAttribute && node.getAttribute('data-onco-commercial-garan') === 'true') {
                return node;
            }
            node = node.parentNode;
        }

        return null;
    }

    function buildModalContent(link) {
        var content = document.createElement('div');
        content.className = 'onco-commercial-garan--modal-content';

        var image = document.createElement('img');
        image.className = 'onco-commercial-garan--label-image';
        image.src = link.getAttribute('data-label-src');
        image.alt = link.getAttribute('data-modal-title') || '';
        content.appendChild(image);

        var paragraph = document.createElement('p');
        paragraph.className = 'onco-commercial-garan--portal-link';
        paragraph.appendChild(
            document.createTextNode((link.getAttribute('data-portal-intro') || '') + ' ')
        );

        var portal = document.createElement('a');
        portal.href = link.href;
        portal.target = '_blank';
        portal.rel = 'nofollow noopener';
        portal.appendChild(
            document.createTextNode(link.getAttribute('data-portal-label') || link.href)
        );
        paragraph.appendChild(portal);
        content.appendChild(paragraph);

        return content;
    }

    document.addEventListener('click', function (event) {
        var link = findNestedLabel(event.target);
        if (!link || !link.getAttribute('data-label-src')) {
            return;
        }

        if (!window.jQuery || !window.jQuery.modal) {
            return;
        }

        event.preventDefault();

        window.jQuery.modal.open(window.jQuery(buildModalContent(link)), {
            title: link.getAttribute('data-modal-title') || '',
            sizing: 'content',
            additionalClass: 'onco-commercial-garan--modal'
        });
    });
})();
