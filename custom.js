/*
$(function(){
  $('#kortbtn').on('click', function(e){
    e.preventDefault();
    $('#kortbtn').fadeOut(300);
    
    $.ajax({
      url: 'ajax-jardir.php',
      type: 'post',
      data: {'action': 'kortlagt', 'userid': '11239528343'},
      success: function(data, status) {
        if(data == "ok") {
          $('#followbtncontainer').html('<p><em>Following!</em></p>');
          var numfollowers = parseInt($('#followercnt').html()) + 1;
          $('#followercnt').html(numfollowers);
        }
      },
      error: function(xhr, desc, err) {
        console.log(xhr);
        console.log("Details: " + desc + "\nError:" + err);
      }
    }); // end ajax call
  });
}*/