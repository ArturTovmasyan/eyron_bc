$(document).ready(function()
{
    setTimeout(function(){
        $("#app_bundle_merge_goal_goal").change(function(element){
            var id = element.val;
            $.get( "/api/v1.0/goals/image/"+id, function( data ) {

                $('#merge-goal-id').text("ID: " + id);
                $('#merge_image').attr("src",data.image_path);
                $('#merge_image').show();
            });
        })
    }, 500);
});