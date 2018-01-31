window.onload = function () {
    /**
     * Plugin admin order
     */
    let orderPA = document.querySelectorAll('.order');
    [].forEach.call(orderPA, function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            if (event.target.classList.contains('order-up')) {
                return orderUp(event.target.parentNode.parentNode.getAttribute('data-order'));
            } else if (event.target.classList.contains('order-down')) {
                return orderDown(event.target.parentNode.parentNode.getAttribute('data-order'));
            }
        });
    });
}

/**
 * Change the position counter of a row.
 *
 * @param elem  Element Node to change.
 * @param toPos int     New position.
 */
function changePos(elem, toPos)
{
    let elemName = elem.getAttribute('data-line')

    elem.setAttribute('data-order', toPos);
    let hiddenInput = document.querySelector('[name="order_'+ elemName +'"]');
    hiddenInput.setAttribute('value', toPos);
}

/**
 * Move a row up or down.
 *
 * @param pos  Element Node to move.
 * @param move int     Move: +1 (down) or -1 (up)
 */
function changeOrder(pos, move)
{
    let newpos = parseInt(pos) + move;
    let lines = document.querySelectorAll('[data-order="'+ pos +'"]');
    let changelines = document.querySelectorAll('[data-order="'+ newpos +'"]');

    // If we go down reverse lines to preserve the rows order
    if (move > 0) {
        lines = [].slice.call(lines).reverse();
    }

    for (let i = 0 ; i < lines.length ; i++) {
        let parent = changelines[0].parentNode;
        changePos(lines[i], newpos);
        changePos(changelines[i], parseInt(pos));
        let changeItem = move < 0 ? changelines[0] : changelines[changelines.length - 1].nextSibling;
        parent.insertBefore(lines[i], changeItem);
    }

}

/**
 * Move a row up in the table.
 *
 * @param pos int row counter.
 *
 * @returns false
 */
function orderUp(pos)
{
    if (pos == 0) {
        return false;
    }
    changeOrder(pos, -1);
    return false;
}

/**
 * Move a row down in the table.
 *
 * @param pos int row counter.
 *
 * @returns false
 */
function orderDown(pos)
{
    let lastpos = document.querySelector('[data-order]:last-child').getAttribute('data-order');
    if (pos == lastpos) {
        return false;
    }

    changeOrder(pos, +1);
    return false;
}
