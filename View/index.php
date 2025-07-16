<div class="col-12" id="layout"></div>
<script>
    (async function () {
        await builder.Storage._ensureReady?.();
        $(document).ready(function(){
            $.ajax({
                url: '/api/groups/fetchAll',
                headers: {'X-CSRF-Authorization': CSRF_KEY},
                type: 'POST',dataType: 'json',
                data: {
                    conditions: [
                        {key: 'isArchived', operator: '<>', value: 1},
                    ]
                },
                error: function(xhr, status, error) {
                    let color = 'info', icon = 'question-circle', title = builder.Locale.get(xhr.statusText), content = builder.Locale.get(xhr.responseText);
                    switch(xhr.status){
                        case 403: color = 'danger'; icon = 'people'; break;
                        case 404: color = 'warning'; icon = 'question-diamond'; break;
                        case 500: color = 'danger'; icon = 'bug'; break;
                    }
                    builder.Component("alert","#layout",{icon:icon,color:color,title:title},function(alert,component){component.content.html('<pre class="m-0 p-2">'+content+'</pre>');});
                },
                success: async function(response) {

                    // Configure Storage
                    builder.Storage.setKey('groups:index');
                    await builder.Storage.set(response);
                    console.log(await builder.Storage.get());

                    // Set Actions
                    var actions = {
                        details:{
                            label:'Details',
                            icon:'eye',
                            action:function(event, table, dt, node, row, data){
                                window.location.href = "/plugin/groups/details?id=" + data.id + "&name=" + data.name;
                            }
                        },
                    };

                    // Set Buttons
                    var buttons = [];

                    // Layout
                    builder.Layout(
                        "list",
                        "#layout",
                        {
                            title: builder.Locale.get('Groups'),
                            icon: 'people',
                            advancedSearch:true,
                            exportTools:true,
                            columnsVisibility:true,
                            selectTools:false,
                            showButtonsLabel: false,
                            dblclick:function(event, table, dt, node, data){
                                actions.details.action(event, table, dt, node, null, data);
                            },
                            actions:actions,
                            buttons:buttons,
                            columnDefs:[
                                { target: 0, visible: false, title: builder.Locale.get('ID'), name: 'id', data: 'id', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(data)
                                    return object.prop('outerHTML');
                                }},
                                { target: 1, visible: true, title: builder.Locale.get('Name'), name: 'name', data: 'name', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(data)
                                    return object.prop('outerHTML');
                                }},
                                { target: 2, visible: true, title: builder.Locale.get('Description'), name: 'description', data: 'description', render: function(data, type, row) {
                                    var object = $(document.createElement('span'))
                                        .addClass('my-2')
                                        .text(data)
                                    return object.prop('outerHTML');
                                }},
                                { target: 3, visible: true, title: builder.Locale.get('Default'), name: 'isDefault', data: 'isDefault', render: function(data, type, row) {
                                    if(data) {
                                        return '<h5><span class="badge text-bg-success"><i class="me-1 bi bi-check"></i>'+builder.Locale.get('Default')+'</span></h5>';
                                    } else {
                                        return '<h5><span class="badge text-bg-danger"><i class="me-1 bi bi-ban"></i>'+builder.Locale.get('Default')+'</span></h5>';
                                    }
                                }},
                            ],
                        },
                        async function(layout, component){

                            // Set container
                            var container = component.card._component.body;

                            // // Add Subscribe Control
                            // var subscribe = $(document.createElement('button')).addClass('position-absolute top-15 end-0 btn btn-sm btn-light m-3 z-3').attr('data-action','subscribe').attr('data-user','<?= $this->Auth->user()->username ?>').text('Subscribe').prependTo(container);
                            // subscribe.icon = $(document.createElement('i')).addClass('bi bi-bell me-1').prependTo(subscribe);

                            // Lower the z-index of the table
                            component.table._component.table.addClass('z-2');

                            // Add Records to Layout
                            for(const [key, record] of Object.entries(await builder.Storage.get('records'))){
                                layout.add(record);
                            }
                        },
                    );
                },
            });
        });
    })();
</script>
