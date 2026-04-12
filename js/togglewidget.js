/**
 * @param string node  The DOM id of the node to show or hide.
 *                     The node that contains the toggle link should be named
 *                     {node}-toggle
 */
function doActionToggle(node, pref_name)
{
    togglePlusMinus(node, pref_name);
    node = node.replace('-toggle', '');
    var el = document.getElementById(node);
    el.hidden = !el.hidden;
    return false;
}

function togglePlusMinus(node, pref_name)
{
    var pref_value,
        el = document.getElementById(node);

    if (el.classList.contains('show')) {
        el.classList.replace('show', 'hide');
        pref_value = 1;
    } else if (el.classList.contains('hide')) {
        el.classList.replace('hide', 'show');
        pref_value = 0;
    }

    HordeCore.doAction('setPrefValue', {
        pref: pref_name,
        value: pref_value
    });
}
