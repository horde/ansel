/**
 * Javascript for handling edit faces actions.
 */
var AnselEditFaces = {

    remove: function(params)
    {
        HordeCore.doAction('deleteFaces', params);
        var el = document.getElementById('face' + params.face_id);
        if (el) {
            el.remove();
        }
    },

    set: function(params)
    {
        params.face_name = document.getElementById('facename' + params.face_id).value;
        HordeCore.doAction('setFaceName', params, {
            callback: function(r) {
                document.getElementById('faces_widget_content').innerHTML = r.response;
            }
        });
    }

}
