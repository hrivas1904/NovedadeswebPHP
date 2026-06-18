//GUARDIAS
$("#btnKpiPromedioGuardias").on("click", function () {
    $("#modalGuardias").modal('show');
});

$("#cardGuardiaGralHeader").on("click", function () {
    $("#cardGuardiaGralBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardGuardiaAdultoHeader").on("click", function () {
    $("#cardGuardiaAdultoBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardGuardiaPedHeader").on("click", function () {
    $("#cardGuardiaPedBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

//INT ESTADIA
$("#btnKpiPromedioInternacion").on("click", function () {
    $("#modalInternacionEst").modal('show');
});

$("#cardEstadiaGeneralHeader").on("click", function () {
    $("#cardEstadiaGeneralBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaStdHeader").on("click", function () {
    $("#cardEstadiaStdBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaDsgHeader").on("click", function () {
    $("#cardEstadiaDsgBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaOtrasHeader").on("click", function () {
    $("#cardEstadiaOtrasBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaUtiaHeader").on("click", function () {
    $("#cardEstadiaUtiaBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaUtipHeader").on("click", function () {
    $("#cardEstadiaUtipBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

$("#cardEstadiaUtinHeader").on("click", function () {
    $("#cardEstadiaUtinBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});

//INT AMB
$("#btnKpiPromedioAmbulatoria").on("click", function () {
    $("#modalAmbulatoria").modal('show');
});

$("#cardAmbHeader").on("click", function () {
    $("#cardAmbBody").toggleClass("d-none");
    $(this).find("i").toggleClass("fa-circle-chevron-down fa-circle-chevron-up");
    $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
});