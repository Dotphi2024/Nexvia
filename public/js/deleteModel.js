$(document).ready(function() {
    $("#deleteModal").on("show.bs.modal", function (event) {
        var button = $(event.relatedTarget); 
        var modelId = button.data("id"); 
        var modal = $(this);
        modal.find("#modelId").val(modelId); 
    });
});