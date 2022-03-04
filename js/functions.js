function insertImageChart(type,type2,field,x,y,xHistory,yHistory,debug) {
    let image = new Image();
    image.src = imagePath + "&type=" + type + "&type2=" + type2;
    image.onload = function() {
        $("#" + field + "-tr").after(
            "<tr><td class='labelrc' colspan='2'>" +
            "<canvas id='headCircCanvas" + type + "' height='" + image.height + "' width='" + image.width + "' />" +
            "</td></tr>"
        );
        let canvas = document.getElementById("headCircCanvas" + type + "");
        let context = canvas.getContext('2d');
        context.drawImage(image,0,0);
        if(x && y) {
            context.beginPath();
            context.strokeStyle = "#FF0000";
            context.arc(x, image.height - y, 4, 0, 2 * Math.PI);
            context.fillStyle = "#FF0000";
            context.fill();
            context.stroke();
        }

        for(let i = 0; i < xHistory.length; i++) {
            if(xHistory[i] != x || yHistory[i] != y) {
                context.beginPath();
                context.strokeStyle = "#0000FF";
                context.arc(xHistory[i], image.height - yHistory[i], 4, 0, 2 * Math.PI);
                context.fillStyle = "#0000FF";
                context.fill();
                context.stroke();
            }
        }

        if(debug) {
            context.beginPath();
            context.strokeStyle = "#FF0000";
            context.rect(debug[0],image.height - debug[1] - debug[3],debug[2],debug[3]);
            context.stroke();
        }
    }

}