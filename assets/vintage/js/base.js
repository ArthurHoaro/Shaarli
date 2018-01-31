// JS
import Awesomplete from 'awesomplete';

// CSS
import '../css/reset.css';
import 'awesomplete/awesomplete.css';
import '../css/shaarli.css';

// Images
import '../img/delete_icon.png';
import '../img/edit_icon.png';
import '../img/favicon.ico';
import '../img/feed-icon-14x14.png';
import '../img/floral_left.png';
import '../img/floral_right.png';
import '../img/private.png';
import '../img/private_16x16.png';
import '../img/private_16x16_active.png';
import '../img/squiggle.png';
import '../img/squiggle_closing.png';

window.onload = function () {
    let awp = Awesomplete.$;
    let autocompleteFields = document.querySelectorAll('input[data-multiple]');
    [].forEach.call(autocompleteFields, function (autocompleteField) {
        let awesomplete = new Awesomplete(awp(autocompleteField), {
            filter: function (text, input) {
                return Awesomplete.FILTER_CONTAINS(text, input.match(/[^ ]*$/)[0]);
            },
            replace: function (text) {
                let before = this.input.value.match(/^.+ \s*|/)[0];
                this.input.value = before + text + " ";
            },
            minChars: 1
        });

        autocompleteField.addEventListener('input', function () {
            let proposedTags = autocompleteField.getAttribute('data-list').replace(/,/g, '').split(' ');
            let reg = /(\w+) /g;
            let match = null;
            while ((match = reg.exec(autocompleteField.value)) !== null) {
                let id = proposedTags.indexOf(match[1]);
                if (id != -1) {
                    proposedTags.splice(id, 1);
                }
            }

            awesomplete.list = proposedTags;
        });
    });
};
