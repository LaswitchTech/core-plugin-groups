<article id="layout"></article>
<script>
    (function () {
        $(document).ready(function(){
            builder.Layout('index',"#layout",{
                url: '/api/groups/fetchAll',
                conditions: [
                    {key: 'isArchived', operator: '<>', value: 1},
                ],
                dblclick: function(event, table, dt, node, data){
                    window.location.href = "/security/groups/details?id=" + data.id + "&name=" + encodeURIComponent(data.name);
                },
                selectTools: false,
                actions: {
                    details:{
                        label:'Details',
                        icon:'eye',
                        action:function(event, table, dt, node, row, data){
                            window.location.href = "/security/groups/details?id=" + data.id + "&name=" + encodeURIComponent(data.name);
                        }
                    },
                    archive:{
                        label:'Archive',
                        icon:'archive',
                        action:function(event, table, dt, node, row, data){}
                    },
                },
                buttons: [],
                columns: [
                    {
                        targets: 0,
                        visible: false,
                        title: builder.Locale.get('ID'),
                        name: 'id',
                        data: 'id',
                        defaultContent: '',
                    },
                    {
                        targets: 1,
                        visible: true,
                        className: 'all',
                        responsivePriority: 1,
                        title: builder.Locale.get('Name'),
                        name: 'name',
                        data: 'name',
                        defaultContent: '',
                    },
                    {
                        targets: 2,
                        visible: true,
                        className: 'min-md',
                        responsivePriority: 10,
                        title: builder.Locale.get('Description'),
                        name: 'description',
                        data: 'description',
                        defaultContent: '',
                    },
                    {
                        targets: 3,
                        visible: false,
                        className: 'min-md',
                        responsivePriority: 100,
                        title: builder.Locale.get('Default'),
                        name: 'isDefault',
                        data: 'isDefault',
                        defaultContent: '',
                        render: function(data, type, row) {
                            return '<h5><span class="badge text-bg-'+((data) ? 'success' : 'danger')+'"><i class="me-1 bi bi-'+((data) ? 'check' : 'ban')+'"></i>'+builder.Locale.get('Default')+'</span></h5>';
                        }
                    },
                ],
            });
        });
    })();
</script>
