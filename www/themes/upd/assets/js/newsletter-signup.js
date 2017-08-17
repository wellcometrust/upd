/**
  * Short piece of code that shows extra fields when the user clicks in the
  * email sign up field.
 **/
document.getElementById("mce-EMAIL").onclick = function(){
  var fieldsToShow = Array.from(document.getElementById("mc_embed_signup").getElementsByClassName("visually-hidden"));

  fieldsToShow.forEach(function(item, index){
    fieldsToShow[index].classList.remove("visually-hidden");
  })
};
