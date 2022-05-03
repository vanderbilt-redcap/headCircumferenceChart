function insertImageChart(chartType,chartDataSet,chartSex,field,x,y,xHistory,yHistory,debug) {
    let image = new Image();

    image.src = HCC_Image_Path + "&chartType=" + chartType + "&chartSex=" + chartSex + "&chartDataSet=" + chartDataSet;
    image.onload = function() {
        $("#" + field + "-tr").after(
            "<tr><td class='labelrc' colspan='2'>" +
            "<canvas id='HC_" + chartType + "_Canvas' height='" + image.height + "' width='" + image.width + "' />" +
            "</td></tr>"
        );
        let canvas = document.getElementById('HC_' + chartType + '_Canvas');
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

var currentAge = null;

function ajaxCallUpdateChart(chartType) {

    // TODO Need to remove old chart before adding new chart
    let currentValue = false;
    if(chartType == "height") {
        currentValue = $("input[name='" + HCC_Height_Field + "']").val();
    }
    else if(chartType == "weight") {
        currentValue = $("input[name='" + HCC_Weight_Field + "']").val();
    }
    else if(chartType == "headCirc") {
        currentValue = $("input[name='" + HCC_Circ_Field + "']").val();
    }

    const queryString = window.location.search;
    console.log(queryString);

    const urlParams = new URLSearchParams(queryString);

    if(currentValue != "" && currentAge !== null) {
        $('#HC_' + chartType + '_Canvas').parent().parent().remove();
        $.ajax({
            url: HCC_Update_Path,
            type: "POST",
            success: function(values) {
                insertImageChart(values.chartType,values.chartDataSet,values.chartSex,values.field,values.x,values.y,values.xHistory,values.yHistory,values.debug);
            },
            data: {
                thisValue: currentValue,
                record: urlParams.get("id"),
                event: urlParams.get("event_id"),
                instrument: urlParams.get("page"),
                repeatInstance: urlParams.get("instance"),
                age: currentAge
            },
            dataType: "json"
        });
    }
}

$(document).ready(function() {
    if(HCC_Height_Field !== null) {
        $("input[name='" + HCC_Height_Field + "']").blur(function() {
            ajaxCallUpdateChart("height");
        });
    }
    if(HCC_Weight_Field !== null) {
        $("input[name='" + HCC_Weight_Field + "']").blur(function() {
            ajaxCallUpdateChart("weight");
        });
    }
    if(HCC_Circ_Field !== null) {
        $("input[name='" + HCC_Circ_Field + "']").blur(function() {
            ajaxCallUpdateChart("headCirc");
        });
    }
    if(HCC_Age_Field !== null) {
        currentAge = $("input[name='" + HCC_Age_Field + "']").val();
        $("input[name='" + HCC_Age_Field + "']").change(function() {
            currentAge = $("input[name='" + HCC_Age_Field + "']").val();
            if(HCC_Height_Field !== null) {
                ajaxCallUpdateChart("height");
            }
            if(HCC_Weight_Field !== null) {
                ajaxCallUpdateChart("weight");
            }
            if(HCC_Circ_Field !== null) {
                ajaxCallUpdateChart("headCirc");
            }
        });
    }
});