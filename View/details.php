<!--
  Core Framework - View File

  @license    MIT (https://mit-license.org/)
  @author     Louis Ouellet <louis@laswitchtech.com>
-->
<div class="col-12" id="layout"></div>
<script>
    $(document).ready(function(){
        $.ajax({
            url: '/endpoint.php/groups/fetch?id=<?= $this->Request->getParams('GET', 'id') ?>',
            type: 'GET',dataType: 'json',
            error: function(xhr, status, error) {
                let color = 'info', icon = 'question-circle', title = builder.Locale.get(xhr.statusText), content = builder.Locale.get(xhr.responseText);
                switch(xhr.status){
                    case 403: color = 'danger'; icon = 'people'; break;
                    case 404: color = 'warning'; icon = 'question-diamond'; break;
                    case 500: color = 'danger'; icon = 'bug'; break;
                }
                builder.Component("alert","#layout",{icon:icon,color:color,title:title},function(alert,component){component.content.html('<pre class="m-0 p-2">'+content+'</pre>');});
            },
            success: function(response) {
                console.log(response);

                // Set the color, icon and label
                var color = ['secondary','primary','success','warning','danger'];
                var icon = ['ban','eye','plus-lg','pencil','trash'];
                var label = ['None','Read','Create','Update','Delete'];

                // Set the element
                var element = $('#layout');

                // Setup the layout
                element.row = $(document.createElement('div')).addClass('row').appendTo(element);
                element.col1 = $(document.createElement('div')).addClass('col-12 col-md-6 col-lg-4').appendTo(element.row);
                element.col2 = $(document.createElement('div')).addClass('col-12 col-md-6 col-lg-8').appendTo(element.row);

                // Create the Details Card
                builder.Component(
                    "card",
                    element.col1,
                    {
                        icon: "people",
                        title: builder.Locale.get('Details'),
                    },
                    function(card,component){

                        // Styling
                        component.body.addClass('d-flex flex-column justify-content-center align-items-center').attr({
                            "title": response.record.description,
                            "data-bs-title": response.record.description,
                            "data-bs-toggle": "tooltip",
                            "data-bs-placement": "bottom",
                        });
                        new bootstrap.Tooltip(component.body);

                        // Insert the group's icon
                        component.body.icon = $(document.createElement('div')).addClass('rounded-circle border border-3 border-light d-flex justify-content-center align-items-center position-relative').css({"height": "256px", "width": "256px"}).appendTo(component.body);
                        component.body.icon.img = $(document.createElement('i')).attr({
                            "class": "bi bi-people",
                            "style": "font-size: 176px;",
                        }).appendTo(component.body.icon);

                        // Insert the group's name
                        component.body.name = $(document.createElement('div')).addClass('position-relative mt-2 text-center').appendTo(component.body);
                        component.body.name.string = $(document.createElement('h2')).attr({
                            "class": "fw-lighter m-0 cursor-default",
                        }).text(response.record.name).appendTo(component.body.name);
                        if(response.record.isDefault){
                            component.body.name.default = $(document.createElement('span')).attr({
                                "class": "badge text-bg-success fs-6 mt-1",
                            }).text(builder.Locale.get('Default')).appendTo(component.body.name);
                        }
                        component.body.name.btn = $(document.createElement('button')).attr({
                            "type": "button",
                            "class": "btn btn-sm btn-warning fs-5 rounded-circle position-absolute",
                            "style": "transition: all 0.5s ease-in-out; height: 48px!important; width: 48px!important; top: calc(50% - 24px); right: -56px;",
                        }).html('<i class="bi bi-pencil"></i>').appendTo(component.body.name);
                        component.body.name.btn.click(function(){
                            builder.Component(
                                "modal",
                                {
                                    onEnter: false,
                                    destroy: true,
                                    icon: "pencil",
                                    title: builder.Locale.get('Edit Group'),
                                    cancel: false,
                                    submit: true,
                                    callback: {
                                        submit: function(element,modal){
                                            element.form.submit();
                                        },
                                    },
                                },
                                function(modal,component){
                                    const componentModal = component;
                                    component.header.addClass('text-bg-warning');
                                    component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                        "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                    });
                                    component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-save me-1').prependTo(component.footer.submit);
                                    component.form = builder.Component(
                                        'form',
                                        component.body,
                                        {
                                            class:{
                                                form: 'row row-cols-3',
                                                field: 'col',
                                            },
                                            callback:{
                                                submit: function(form){

                                                    // AJAX Request
                                                    $.ajax({
                                                        url: '/endpoint.php/groups/update?id='+response.record.id,
                                                        type: 'POST',dataType: 'json',
                                                        data: form.val(),
                                                        success: function(response) {

                                                            // Update the CSRF Token
                                                            CSRF_KEY = response.CSRF.key;
                                                            CSRF_TOKEN = response.CSRF.token;

                                                            // Hide the modal
                                                            modal.hide();
                                                        }
                                                    });
                                                },
                                            },
                                        },
                                        function(form,component){

                                            // csrf
                                            form.add(
                                                {
                                                    name: CSRF_KEY,
                                                    label: 'csrf',
                                                    icon: 'hash',
                                                    type: 'hidden',
                                                    value: CSRF_TOKEN,
                                                },
                                                function(input,form){
                                                    input.css('display','none');
                                                },
                                            );

                                            // name
                                            form.add(
                                                {
                                                    name: 'name',
                                                    label: builder.Locale.get('Name'),
                                                    icon: 'hash',
                                                    type: 'text',
                                                    value: response.record.name,
                                                    class: {
                                                        field: 'col-12 mb-3',
                                                    },
                                                }
                                            );

                                            // description
                                            form.add(
                                                {
                                                    name: 'description',
                                                    label: builder.Locale.get('Description'),
                                                    icon: 'hash',
                                                    type: 'textarea',
                                                    value: response.record.description,
                                                    class: {
                                                        field: 'col-12 mb-3',
                                                    },
                                                },
                                                function(input,form){
                                                    input.input.addClass('min-vh-20');
                                                },
                                            );

                                            // isDefault
                                            form.add(
                                                {
                                                    name: 'isDefault',
                                                    label: builder.Locale.get('Set as Default'),
                                                    icon: 'hash',
                                                    type: 'switch',
                                                    value: response.record.isDefault,
                                                    class: {
                                                        field: 'col-12',
                                                    },
                                                },
                                            );

                                            // Show the modal
                                            modal.show();
                                        },
                                    );
                                },
                            );
                        });
                    },
                );

                // Create a Tabs component
                builder.Component(
                    "tabs",
                    element.col2,
                    {
                        class: {
                            navbar: 'nav-pills',
                        },
                    },
                    function(tabs,card){
                        card._component.body.removeClass('card-body');
                        tabs.add(
                            'users',
                            {
                                icon: "person",
                                label: builder.Locale.get("Users"),
                            },
                            function(tab,nav){
                                var actions = {
                                    details:{
                                        label:'Details',
                                        icon:'eye',
                                        action:function(event, table, dt, node, row, data){
                                            window.location.href = "/plugin/users/details?id=" + data.id + "&name=" + data.username;
                                        }
                                    },
                                    remove:{
                                        label:'Remove',
                                        icon:'trash',
                                        action:function(event, table, dt, node, row, data){

                                            // Update the table
                                            table.delete(row);

                                            // Retrieve the users
                                            var users = [];
                                            for(const [key, record] of Object.entries(table.data())){
                                                if(data.id !== record.id){
                                                    users.push(record.id);
                                                }
                                            }

                                            // Set the AJAX DATA
                                            var ajaxData = {
                                                users: JSON.stringify(users),
                                            };
                                            ajaxData[CSRF_KEY] = CSRF_TOKEN;

                                            // AJAX Request
                                            $.ajax({
                                                url: '/endpoint.php/groups/update?id='+response.record.id,
                                                type: 'POST',dataType: 'json',
                                                data: ajaxData,
                                                success: function(response) {

                                                    // Update the CSRF Token
                                                    CSRF_KEY = response.CSRF.key;
                                                    CSRF_TOKEN = response.CSRF.token;
                                                }
                                            });
                                        },
                                    },
                                };
                                var buttons = [
                                    {
                                        className : 'btn-success',
                                        init: function (dt, node){
                                            $(node).removeClass('btn-secondary');
                                        },
                                        text: '<i class="bi bi-plus-lg me-2"></i>'+builder.Locale.get('Add User'),
                                        action:function(event, dt, node, config){

                                            // AJAX Request
                                            $.ajax({
                                                url: '/endpoint.php/groups/users',
                                                type: 'GET',dataType: 'json',
                                                success: function(users) {

                                                    // Retrieve existing members
                                                    var members = []
                                                    for(const [key, record] of Object.entries(dt.data().toArray())){
                                                        members.push(record.id);
                                                    }

                                                    // Build options
                                                    var options = [];
                                                    for(const [key, user] of Object.entries(users)){
                                                        if($.inArray(user.id, members) === -1){
                                                            options.push({id: user.id, text: user.username+' - '+user.vcard.name});
                                                        }
                                                    }

                                                    // Create a modal with a form
                                                    builder.Component(
                                                        "modal",
                                                        {
                                                            onEnter: false,
                                                            destroy: true,
                                                            icon: "plus-lg",
                                                            title: builder.Locale.get('Add User'),
                                                            cancel: false,
                                                            submit: true,
                                                            callback: {
                                                                submit: function(element,modal){
                                                                    element.form.submit();
                                                                },
                                                            },
                                                        },
                                                        function(modal,component){
                                                            const componentModal = component;
                                                            component.header.addClass('text-bg-success');
                                                            component.footer.submit.addClass('btn-success').removeClass('btn-link').attr({
                                                                "style": "border-bottom-right-radius: var(--bs-modal-inner-border-radius) !important;border-bottom-left-radius: var(--bs-modal-inner-border-radius) !important;",
                                                            }).text(builder.Locale.get('Add'));
                                                            component.footer.submit.icon = $(document.createElement('i')).addClass('bi bi-plus-lg me-1').prependTo(component.footer.submit);
                                                            component.form = builder.Component(
                                                                'form',
                                                                component.body,
                                                                {
                                                                    class:{
                                                                        form: 'row row-cols-3',
                                                                        field: 'col',
                                                                    },
                                                                    callback:{
                                                                        val: function(values){
                                                                            return parseInt(values.user);
                                                                        },
                                                                        submit: function(form){

                                                                            // Add the record to the table
                                                                            dt.row.add(users[form.val()]).draw();

                                                                            // Add the user to the list of members
                                                                            members.push(form.val());

                                                                            // Set the AJAX DATA
                                                                            var ajaxData = {
                                                                                users: members,
                                                                            };
                                                                            ajaxData[CSRF_KEY] = CSRF_TOKEN;

                                                                            // AJAX Request
                                                                            $.ajax({
                                                                                url: '/endpoint.php/groups/update?id='+response.record.id,
                                                                                type: 'POST',dataType: 'json',
                                                                                data: ajaxData,
                                                                                success: function(response) {

                                                                                    // Update the CSRF Token
                                                                                    CSRF_KEY = response.CSRF.key;
                                                                                    CSRF_TOKEN = response.CSRF.token;

                                                                                    // Hide the modal
                                                                                    modal.hide();
                                                                                }
                                                                            });
                                                                        },
                                                                    },
                                                                },
                                                                function(form,component){

                                                                    // user
                                                                    form.add(
                                                                        {
                                                                            name: 'user',
                                                                            label: builder.Locale.get('User'),
                                                                            icon: 'people',
                                                                            type: 'select2',
                                                                            options: options,
                                                                            modal: componentModal,
                                                                            class: {
                                                                                field: 'col-12',
                                                                            },
                                                                        }
                                                                    );

                                                                    // Show the modal
                                                                    modal.show();
                                                                },
                                                            );
                                                        },
                                                    );
                                                }
                                            });
                                        },
                                    }
                                ];
                                builder.Component(
                                    "table",
                                    tab,
                                    {
                                        class: {
                                            buttons: "px-4 pt-4",
                                            table: "border-top",
                                            footer: "px-4 pt-2 pb-4",
                                        },
                                        showButtonsLabel: false,
                                        selectTools:false,
                                        actions:actions,
                                        dblclick:function(event, table, dt, node, data){
                                            actions.details.action(event, table, dt, node, null, data);
                                        },
                                        datatable:{
                                            columnDefs:[
                                                { target: 0, visible: false, responsivePriority: 1000, title: builder.Locale.get('ID'), name: 'id', data: 'id' },
                                                { target: 1, visible: true, responsivePriority: 1, title: builder.Locale.get('Username'), name: 'username', data: 'username' },
                                            ],
                                            buttons: buttons,
                                        },
                                    },
                                    function(table,component){
                                        for(const [key, record] of Object.entries(response.record.users ?? {})){
                                            table.add(record);
                                        }
                                    },
                                );
                            },
                        );
                        tabs.add(
                            'activities',
                            {
                                icon: "activity",
                                label: builder.Locale.get("Activity"),
                            },
                            function(tab,nav){
                                tab.addClass('px-4 py-3');
                                EventFeed(response.record.events ?? {}, tab);
                            },
                        );
                    },
                );
            },
        });
    });
</script>
