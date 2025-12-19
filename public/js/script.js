window.onload = function(){
    const sidebar = document.querySelector(".sidebar");
    const closeBtn = document.querySelector("#btn");
    const searchBtn = document.querySelector(".bx-search")

    closeBtn.addEventListener("click",function(){
        sidebar.classList.toggle("open")
        menuBtnChange()
    })

    searchBtn.addEventListener("click",function(){
        sidebar.classList.toggle("open")
        menuBtnChange()
    })

    function menuBtnChange(){
        if(sidebar.classList.contains("open")){
            closeBtn.classList.replace("bx-menu","bx-menu-alt-right")
        }else{
            closeBtn.classList.replace("bx-menu-alt-right","bx-menu")
        }
    }
}

document.querySelectorAll('.submenu input').forEach(input => {
    input.addEventListener('change', () => {
        if (input.checked) {
            document.querySelectorAll('.submenu input').forEach(other => {
                if (other !== input) other.checked = false;
            });
        }
    });
});


toggleBtn.addEventListener('click', () => {
    sidebar.classList.toggle('open');

    if (!sidebar.classList.contains('open')) {
        document.querySelectorAll('.submenu input').forEach(i => i.checked = false);
    }
});
