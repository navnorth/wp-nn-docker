jQuery(document).ready(function ($) {
 $('#csv_upload').click(function() {
   // var csvFile = $("#fileToUpload").val();
    var file_data = $('#fileToUpload').prop('files')[0];   
    var form_data = new FormData();                  
    form_data.append('file', file_data);
    form_data.append('action', 'my_action');   
    $.ajax({
              type : "POST", 
              url: ajaxurl,
              data:form_data,
              contentType: false,
              enctype: 'multipart/form-data',
              processData: false,
              success:function(data) {
                console.log(data)
              },
              error: function(errorThrown){
                  console.log(errorThrown);
              }
          });
    });
});