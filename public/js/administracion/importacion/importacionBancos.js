$(document).ready(function () {
    $(document).on("click", ".btn-opcion-banco", function () {
        const $btn = $(this);

        $(".btn-opcion-banco").removeClass("active");
        $btn.addClass("active");
    });
});

$(document).ready(function () {
    $(document).on("click", ".btn-opcion-import", function () {
        const $btn = $(this);

        $(".btn-opcion-import").removeClass("active");
        $btn.addClass("active");
    });
});
