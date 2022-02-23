function insertImageChart(type,field) {
    $("#" + field + "-tr").after(
        "<tr><td class='labelrc' colspan='2'><image src='" + headCircImagePath + "&type=" + type + "' /></td></tr>"
    );
}