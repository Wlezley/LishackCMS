const toggler = document.querySelector(".btn");
toggler.addEventListener("click",function(){
    document.querySelector("#sidebar").classList.toggle("collapsed");
    document.querySelector("#content").classList.toggle("collapsed");
    document.querySelector("#content").classList.toggle("fullsize");
    document.querySelector("#menu-switch").classList.toggle("collapsed");
    document.querySelector("#menu-switch").classList.toggle("fullsize");
});
