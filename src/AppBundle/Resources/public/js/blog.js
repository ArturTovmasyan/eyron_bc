var $collectionHolder;

// setup an "add a tag" link
var $addTagLink = $('<div class=" btn-group btn-group-sm"><button type="button" class="btn btn-xs add-button">Add text or goal</button></div>'),
    $newLinkLi = $('<p style="margin-top: 12px;" class="add-blog"></p>').append($addTagLink),
    url = ((window.location.pathname.indexOf('app_dev.php') === -1) ? "/" : "/app_dev.php/") + 'api/v1.0/goals/get-autocomplete-items',
    uniqId,
    goalId = null,
    goalTitle = '',
    select2TegIds = [];


jQuery(document).ready(function() {

    // Get the ul that holds the collection of tags
    $collectionHolder = $('ul.blog');

    // add the "add a tag" anchor and li to the tags ul
    $collectionHolder.append($newLinkLi);

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $collectionHolder.data('index', $collectionHolder.find(':input').length);

    // $('input[type="file"]').change(function(){
    //     var f = this.files[0];
    //
    //     var reader = new FileReader();
    //
    //     // Closure to capture the file information.
    //     reader.onload = (function(theFile) {
    //         return function(e) {
    //             var img = $('<img style="width: 270px;height: 200px;margin-bottom: 13px;" src="' + e.target.result + '" alt="goal image" id="__file">');
    //             img.prependTo($('input[type="file"]').parent())
    //         };
    //     })(f);
    // });

    for(var index = 0; index < $collectionHolder.data('index'); index++){
        if($("div[id$='_" + index+"_type']").length){
            simpleTypeByIndex(index, 'div[id$="_bl_multiple_blog_'+index+'"');
            addTagFormDeleteLink($('div[id$="_bl_multiple_blog_'+index+'"'));
        }
        var blogElement = $("div[id$='_bl_multiple_blog_" + index+"']");
        if(blogElement.length){
            blogElement.addClass('blog');
        }
    }

    $( "ul.blog" ).sortable({
        items: "> div",
        handle: 'div.drag',
        appendTo: document.body
    });
    // $( "ul.blog" ).disableSelection();

    $addTagLink.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // add a new tag form (see next code block)
        addTagForm($collectionHolder, $newLinkLi);
    });
});

function addTagForm($collectionHolder, $newLinkLi) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);
    $(newForm).find("div[id$='_bl_multiple_blog_" + index+"']").addClass('blog');

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $(newForm);
    addTagFormDeleteLink($newFormLi);
    $newLinkLi.before($newFormLi);
    simpleTypeByIndex(index, newForm);
}

function addTagFormDeleteLink($newFormLi)
{
    var $removeFormA = $('<a class="delete-link btn btn-danger" href="#">Delete</a>');
    var $dragElement = $('<div class="drag btn btn-info"><i class="fa fa-arrows" aria-hidden="true"></i></div>');
    $newFormLi.append($removeFormA);
    $newFormLi.prepend($dragElement);

    $removeFormA.on('click', function(e) {
        // prevent the link from creating a "#" on the URL
        e.preventDefault();

        // remove the li for the tag form
        $newFormLi.remove();
    });
}

function simpleTypeByIndex(index, newForm) {
    select2TegIds[index] = '#' + $(newForm).find( "input[id$='_" + index+"_goal']" ).last()[0].id;
    uniqId = select2TegIds[index].slice(1,select2TegIds[index].indexOf('_bl_multiple'));
    var type = $("select[id$='_" + index+"_type']").val();
    if(type == 'goal'){
        $("textarea[id$='_" + index+"_content']").parent().parent().hide();
        goalId = $("textarea[id$='_" + index+"_content']").val();
        if(goalId){

            $.get( "/api/v1.0/goals/image/"+goalId, function( data ) {
                var img = $("img[id='_" + index+"_goal']");
                if(img.length){
                    img.attr("src",data.image_path);
                } else {
                    img = $('<img style="width: 270px;height: 200px;margin-bottom: 13px;" src="' + data.image_path + '" alt="goal image" id="_'+index+'_goal">');
                    var div = $("div[id$='_" + index+"_goal']").first();
                    img.prependTo(div);
                }

            });
        }

        addAutocmplate(select2TegIds[index], index, goalId);
    } else {
        $(select2TegIds[index]).parent().parent().hide();
    }

    $("select[id$='_" + index+"_type']").change(function(ev){
        var choice = ev.target.value;
        if(choice == 'goal'){
            $(select2TegIds[index]).parent().parent().show();
            goalId = $("select[id$='_" + index+"_goal']").val();
            if(goalId){
                $("textarea[id$='_" + index+"_content']").val(goalId);
            }
            $("div[id$='_" + index+"_content']").hide();
            addAutocmplate(select2TegIds[index], index, goalId);
        } else if(choice == 'text'){
            $("textarea[id$='_" + index+"_content']").val('');
            $("div[id$='_" + index+"_content']").show();
            $(select2TegIds[index]).parent().parent().hide();
        }
    });

}

function toHiddenList(hiddenId)
{
    console.log(hiddenId);
    $(".bl_list_hidden").val(0);
    $("#"+hiddenId).val(1);
}


function addAutocmplate(element, index, currentId) {
    var autocompleteInput = $(element);
    autocompleteInput.select2({placeholder: '', // allowClear needs placeholder to work properly
        allowClear: true,
        enable: true,
        readonly: false,
        minimumInputLength: 3,
        multiple: false,
        width: '',
        dropdownAutoWidth: false,
        containerCssClass: ' form-control',
        dropdownCssClass: '',
        ajax: {
            url:  url,
            dataType: 'json',
            quietMillis: 100,
            cache: false,
            data: function (term, page) { // page is the one-based page number tracked by Select2
                return {
                    //search term
                    'text': term,
                };
            },
            results: function (data, page) {
                // notice we return the value of more so Select2 knows if more results can be loaded
                return {results: data.items, more: data.more};
            }
        },
        formatResult: function (item) {
            return '<div class="">'+item.label+'<\/div>';// format of one dropdown item
        },
        formatSelection: function (item) {
            goalId = item.id;
                $("textarea[id$='_" + index+"_content']").val(goalId);

                $.get( "/api/v1.0/goals/image/"+goalId, function( data ) {
                    var img = $("img[id='_" + index+"_goal']");
                    if(img.length){
                        img.attr("src",data.image_path);
                    } else {
                        img = $('<img style="width: 270px;height: 200px;margin-bottom: 13px;" src="' + data.image_path + '" alt="goal image" id="_'+index+'_goal">');
                        img.prependTo($("div[id$='_" + index+"_goal']").first())
                    }

                });
            return item.label;// format selected item '<b>'+item.label+'</b>';
        },
        escapeMarkup: function (m) { return m; } // we do not want to escape markup since we are displaying html in results
    });

    autocompleteInput.on('change', function(e) {

        // remove input
        if (undefined !== e.removed && null !== e.removed) {
            var removedItems = e.removed;

            if(!$.isArray(removedItems)) {
                removedItems = [removedItems];
            }

            var length = removedItems.length;
            for (var i = 0; i < length; i++) {
                el = removedItems[i];
                $('#' + uniqId + '_place_hidden_inputs_wrap input:hidden[value="'+el.id+'"]').remove();
            }                }

        // add new input
        var el = null;
        if (undefined !== e.added) {

            var addedItems = e.added;

            if(!$.isArray(addedItems)) {
                addedItems = [addedItems];
            }

            var length = addedItems.length;
            for (var i = 0; i < length; i++) {
                el = addedItems[i];
                $('#' + uniqId + '_place_hidden_inputs_wrap').append('<input type="hidden" name="'+ uniqId + '[goal][]" value="'+el.id+'" />');
            }                }
    });

    // Initialise the autocomplete
    var data = [];

    if(currentId){
        $.get( "/api/v1.0/goals/title/"+currentId, function( data ) {
            goalTitle = data.title;
            data = {id: currentId, label: goalTitle};
            autocompleteInput.select2('data', data);
        });

    }

    // remove unneeded autocomplete text input before form submit
    $(element).closest('form').submit(function()
    {
        $(element).remove();
        return true;
    });
};
