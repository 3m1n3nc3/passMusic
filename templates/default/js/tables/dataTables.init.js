$(document).ready(function () {
    $('#releaseTable').DataTable({
        'processing': true,
        'serverSide': true,
        'serverMethod': 'post',
        'ajax': {
            'url': site_url + '/connection/dataTables.php?fetch=releases'
        },
        'columns': [ 
            {data: 'id'},
            {data: 'title'},
            {data: 'release_id'},
            {data: 'status'},
            {data: 'upc'},
            {data: 'c_line'},
            {data: 'p_line'},
            {data: 's_genre'}
        ],
        searching: false, paging: true, info: false
    });  

    function setCellTitle() {
        $('td').each(function() {
            let title = $(this).text();
            $(this).attr('title', title);
        });
    }


});

function deleteIt(type, id) {
    // $.ajax({
    //    type: "POST",
    //    url: siteUrl+"/connection/delete.php",
    //    data: "id="+id+"&type="+type,
    //    cache: false,
    //    success: function(html) {
    //       if(type == 0) {
    //          $('#sche-message').html(html);
    //          $('#schedule_'+id).fadeOut(500, function() { $('#schedule_'+id).remove(); });
    //       }
    //    }
    // });
}

