/**
  * Short piece of code that shows extra fields when the user clicks in the
  * email sign up field.
 **/
if (document.getElementById("mce-EMAIL").length) {
  document.getElementById("mce-EMAIL").onclick = function(){
    var fieldsToShow = Array.from(document.getElementById("mc_embed_signup").querySelectorAll("input, div, fieldset, .form-checkbox+label"));

    fieldsToShow.forEach(function(item, index){
      fieldsToShow[index].classList.remove("visually-hidden");
    });
  };
}
