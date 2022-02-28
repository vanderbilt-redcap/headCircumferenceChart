function insertImageChart(type,field,x,y) {
    let image = new Image();
    image.src = headCircImagePath + "&type=" + type;
    image.onload = function() {
        $("#" + field + "-tr").after(
            "<tr><td class='labelrc' colspan='2'>" +
            "<canvas id='headCircCanvas' height='" + image.height + "' width='" + image.width + "' />" +
            "</td></tr>"
        );
        let canvas = document.getElementById("headCircCanvas");
        let context = canvas.getContext('2d');
        context.drawImage(image,0,0);
        context.strokeStyle = "#FF0000";
        context.beginPath();
        context.arc(x, image.height - y, 4, 0, 2 * Math.PI);
        context.fillStyle = "#FF0000";
        context.fill();
        context.stroke();
    }

}