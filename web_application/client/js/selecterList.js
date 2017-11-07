/**
 * Created by chamathsilva on 1/22/16.
 */


    //Select list generator
    //option must include  helpText,dataArray (josn arry key must be ValeID and LableID)

    jQuery.fn.extend({
        selectList: function (optoins) {
            var select =  $(this);
            select.html("<option value=''>"+optoins.helpText+"</option>");
            $.each(optoins.dataArray, function(i) {
                var value = String(optoins.dataArray[i].ValeID);
                var label = String(optoins.dataArray[i].LableID);

                select.append('<option value="' + value + '">'+  label + '</option>');

            });
        },



        selectListWithClickEvent: function (optoins) {
            var select =  $(this);
            select.html("<option value=''>"+optoins.helpText+"</option>");
            $.each(optoins.dataArray, function(i) {
                var value = String(optoins.dataArray[i].ValeID);
                var label = String(optoins.dataArray[i].LableID);

                var lat = String(optoins.dataArray[i].Lat);
                var lon = String(optoins.dataArray[i].Lon);


                select.append('<option data-lat="'+ lat +'" data-lon="'+ lon +'" value="' + value + '">'+  label + '</option>');

            });
        },


        selectListWithTitle: function (optoins) {
            var select =  $(this);
            //select.html("<option title='' value=''>"+optoins.helpText+"</option>");
            select.html('<option title="'+ optoins.helpText+'" value="">'+  optoins.helpText + '</option>');
            $.each(optoins.dataArray, function(i) {
                var value = String(optoins.dataArray[i].ValeID);
                var label = String(optoins.dataArray[i].LableID);


                select.append('<option title="'+ value +'" value="' + value + '">'+  label + '</option>');

            });
        }







    });





    /*
    How to call above function

     $.getJSON( "../../controllers/DBfunctions/JsonAPI.php",{funtionName : "getUserTypeList"}, function( data ) {
     $('#Employeetype').selectList({
     helpText : 'Select Employee type',
     dataArray : data
     });

     });



     */
