builder.add('layouts','group', class extends builder.ComponentClass {

    _init(){
        this._properties = {
            class: {
                component: null,
            },
            endpoint: null,
            table: 'groups',
            id: null,
            disable: [],
            interval: 15000,
            autoStart: false,
            callback: {},
        };
        this._data = null;
        this._tabs = null;
        this._cards = {};
        this._widgets = {};
        this._interval = null;
    }

    _create(){

        // Set Self
        const self = this;

        // Create Component
        this._component = $(document.createElement('div')).attr({
            'id': 'group' + this._id,
            'class': 'group-layout',
        });
        this._component.id = this._component.attr('id');

        // Add Class
        if(this._properties.class.component){
            this._component.addClass(this._properties.class.component);
        }

        // Retrieve Records
        API.endpoint(self._properties.endpoint).execute(function(response){

            // Set Data
            self._data = response;
            self._properties.id = self._data.record.id;

            // Setup the layout
            self._component.row = $(document.createElement('div')).appendTo(self._component);
            self._component.details = $(document.createElement('div')).addClass('group-details').appendTo(self._component.row);
            self._component.col2 = $(document.createElement('div')).appendTo(self._component.row);

            // Create User Block
            self._component.details.avatar = $(document.createElement('div')).attr({
                'class': 'avatar cursor-pointer',
            }).appendTo(self._component.details).click(function(){
                self.edit();
            });
            self._component.details.avatar.i = $(document.createElement('i')).attr({
                'class': 'bi bi-people',
            }).appendTo(self._component.details.avatar);
            self._component.details.meta = $(document.createElement('div')).addClass('meta').appendTo(self._component.details);
            self._component.details.meta.group = $(document.createElement('button')).attr({
                'class': 'group btn btn-link text-decoration-none',
                'type': 'button'
            }).text(self._data.record.name).appendTo(self._component.details.meta).click(function(){
                self.edit();
            });
            if(self._data.record.isDefault){
                self._component.details.meta.group.append($(document.createElement('span')).attr({
                    'class': 'badge rounded-pill p-2 text-bg-primary ms-2',
                    'data-bs-toggle': 'tooltip',
                    'data-bs-title': self._builder.Locale.get('Default Group'),
                }).html('<i class="bi bi-check-lg"></i>'));
                new bootstrap.Tooltip(self._component.details.meta.group.find('.badge'));
            }
            self._component.details.meta.description = $(document.createElement('p')).attr({
                'class': 'm-0',
            }).text(self._data.record.description).appendTo(self._component.details.meta);
            self._component.details.meta.metadata = $(document.createElement('div')).addClass('metadata').appendTo(self._component.details.meta);
            const created = new Date(self._data.record.created ?? new Date().toISOString());
            self._component.details.meta.metadata.created = $(document.createElement('div')).attr({
                'class': 'metadata-item',
                'data-bs-toggle': 'tooltip',
                'data-bs-title': created.toLocaleString(),
            }).appendTo(self._component.details.meta.metadata);
            new bootstrap.Tooltip(self._component.details.meta.metadata.created);
            self._component.details.meta.metadata.created.label = $(document.createElement('span')).text(self._builder.Locale.get('Created')).addClass('me-1').appendTo(self._component.details.meta.metadata.created);
            self._component.details.meta.metadata.created.icon = $(document.createElement('i')).addClass('bi bi-clock me-1').appendTo(self._component.details.meta.metadata.created);
            self._component.details.meta.metadata.created.timeago = $(document.createElement('time')).attr({
                'class': 'timeago',
                'datetime': self._data.record.created ?? new Date().toISOString(),
            }).appendTo(self._component.details.meta.metadata.created).timeago();

            // Create a Tabs component
            self._builder.Component(
                "tabs",
                self._component.col2,
                {
                    class: {
                        navbar: 'nav-pills',
                    },
                },
                function(tabs,card){

                    // Set tabs
                    self.tabs(tabs);

                    // Styling
                    card._component.addClass('group-content');
                    card._component.body.removeClass('card-body');

                    // Users
                    tabs.add(
                        'users',
                        {
                            icon: "person",
                            label: self._builder.Locale.get("Users"),
                            class: {
                                tab: 'group-members-feed',
                            },
                        },
                        function(tab,nav){
                            self._cards.users = tab;
                            self._widgets.users = self._builder.Component(
                                'datatable',
                                tab,
                                {
                                    class: {
                                        buttons: 'group-members-controls',
                                        table: 'group-members-table',
                                        footer: 'group-members-footer',
                                    },
                                    actions: {
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

                                                // Retrieve the users
                                                var users = [];
                                                for(const [key, record] of Object.entries(table.data())){
                                                    if(data.id !== record.id){
                                                        users.push(record.id);
                                                    }
                                                }

                                                // AJAX Request
                                                API.endpoint('/groups/update?id='+self._properties.id).data({users: JSON.stringify(users)}).execute(function(response){
                                                    table.delete(row);
                                                });
                                            },
                                        },
                                    },
                                    dblclick: function(event, table, dt, node, data){
                                        window.location.href = "/plugin/users/details?id=" + data.id + "&name=" + data.username;
                                    },
                                    standardSearch: true,
                                    advancedSearch: true,
                                    showButtonsLabel: false,
                                    datatable: {
                                        responsive: {
                                            breakpoints: [
                                                { name: 'xl', width: Infinity },
                                                { name: 'lg', width: 1400 },
                                                { name: 'md', width: 992 },
                                                { name: 'sm', width: 768 },
                                                { name: 'xs', width: 576 },
                                                { name: 'xxs', width: 0 }
                                            ]
                                        },
                                        buttons: [
                                            {
                                                className : 'btn-success',
                                                init: function (dt, node){
                                                    $(node).removeClass('btn-secondary');
                                                },
                                                text: '<i class="bi bi-plus-lg"></i>',
                                                action:function(event, dt, node, config){

                                                    // Create the Modal
                                                    self._builder.Component(
                                                        "modal",
                                                        {
                                                            onEnter: false,
                                                            icon: "plus-lg",
                                                            title: builder.Locale.get('Add Member'),
                                                            color: 'success',
                                                            callback: {
                                                                load: function(component, modal){
                                                                    return new Promise((resolve, reject) => {
                                                                        try {

                                                                            // Set the parent
                                                                            const parent = component.dialog;

                                                                            // Styling
                                                                            component.body.addClass('p-0');

                                                                            // AJAX Request
                                                                            API.endpoint('/auth/users').execute(function(response){

                                                                                // Retrieve existing members
                                                                                var members = []
                                                                                for(const [key, row] of Object.entries(dt.data().toArray())){
                                                                                    members.push(row.id);
                                                                                }

                                                                                // Build options
                                                                                var options = [];
                                                                                for(const [key, user] of Object.entries(response.records)){
                                                                                    if($.inArray(user.id, members) === -1){
                                                                                        options.push({id: user.id, text: user.username+' - '+user.vcard.name});
                                                                                    }
                                                                                }

                                                                                // Create the Form
                                                                                self._builder.Utility(
                                                                                    'form',
                                                                                    component.body,
                                                                                    {
                                                                                        callback: {
                                                                                            val: function(values){
                                                                                                return parseInt(values.user);
                                                                                            },
                                                                                            submit: function(form){

                                                                                                // Show the modal spinner
                                                                                                modal.spinner(true);

                                                                                                // Add the user to the list of members
                                                                                                members.push(form.val());

                                                                                                // AJAX Request
                                                                                                API.endpoint('/groups/update?id='+self._properties.id).data({users: members}).execute(function(response){

                                                                                                    // Add the record to the table
                                                                                                    dt.row.add(response.dependencies.users[form.val()]).draw();

                                                                                                    // Hide the modal
                                                                                                    modal.hide();
                                                                                                },function(xhr, status, error){
                                                                                                    modal.hide();
                                                                                                });
                                                                                            },
                                                                                        },
                                                                                    },
                                                                                    function(form,component){

                                                                                        // Add event listener on the modal submit button
                                                                                        parent.content.footer.submit.click(function(e){
                                                                                            e.preventDefault();
                                                                                            e.stopPropagation();
                                                                                            form.submit();
                                                                                        });

                                                                                        // user
                                                                                        form.add(
                                                                                            'select2',
                                                                                            {
                                                                                                name: 'user',
                                                                                                label: builder.Locale.get('User'),
                                                                                                placeholder: self._builder.Locale.get('Select a user'),
                                                                                                options: options,
                                                                                                class: {
                                                                                                    component: 'bg-gray-200 p-3 py-2 rounded-0',
                                                                                                },
                                                                                            }
                                                                                        );

                                                                                        // Resolve the promise
                                                                                        resolve();
                                                                                    }
                                                                                );
                                                                            },function(xhr, status, error){
                                                                                modal.hide();
                                                                                reject(error);
                                                                            });
                                                                        } catch (error) {
                                                                            reject(error);
                                                                        }
                                                                    });
                                                                },
                                                            },
                                                        },
                                                        function(modal,component){

                                                            // Show the modal
                                                            modal.show();
                                                        },
                                                    );
                                                },
                                            }
                                        ],
                                        columnDefs: [
                                            {
                                                targets: 0,
                                                visible: false,
                                                title: builder.Locale.get('ID'),
                                                name: 'id',
                                                data: 'id',
                                            },
                                            {
                                                targets: 1,
                                                visible: true,
                                                title: builder.Locale.get('Username'),
                                                className: 'all',
                                                name: 'username',
                                                data: 'username',
                                                defaultContent: '',
                                                responsivePriority: 1,
                                            },
                                        ],
                                        initComplete: function(param) {
                                            $(param.nTableWrapper).find('.dataTables_filter input').attr({
                                                'placeholder': self._builder.Locale.get('Search...'),
                                            });
                                        },
                                    },
                                },
                                function(datatable, component){
                                    for(const [key, user] of Object.entries(self._data.dependencies.users ?? {})){
                                        datatable.add(user);
                                    }
                                }
                            )
                        },
                    );

                    // Notes
                    if(self._data.extensions.includes('notes') && !self._properties.disable.includes('notes')){

                        // Add the tab
                        tabs.add(
                            'notes',
                            {
                                icon: "stickies",
                                label: self._builder.Locale.get("Notes"),
                            },
                            function(tab,nav){
                                self._cards.notes = tab;
                                self._widgets.notes = self._builder.Widget('notes',tab,{data: self._data.dependencies.notes ?? {},targetTable: self._properties.table,targetId: self._properties.id})
                            },
                        );
                    }

                    // Event
                    if(self._data.extensions.includes('event') && !self._properties.disable.includes('event')){

                        // Add the Event tab
                        tabs.add(
                            'event',
                            {
                                icon: "activity",
                                label: self._builder.Locale.get("Activity"),
                            },
                            function(tab,nav){
                                self._cards.event = tab;
                                self._widgets.event = self._builder.Widget("events",tab,{data: self._data.dependencies.event ?? {},targetTable: self._properties.table,targetId: self._properties.id});
                            },
                        );
                    }
                },
            );
        });
    }

    edit(){

        // Set Self
        const self = this;

        // Create the Modal
        this._builder.Component(
            "modal",
            {
                icon: "pencil",
                color: 'warning',
                title: builder.Locale.get('Edit'),
                size: 'lg',
                callback: {
                    load: function(component, modal){
                        return new Promise((resolve, reject) => {
                            try {
                                // Set the parent
                                const parent = component.dialog;

                                // Styling
                                component.body.addClass('p-0');

                                // Create the Form
                                self._builder.Utility(
                                    'form',
                                    component.body,
                                    {
                                        callback: {
                                            submit: function(form){

                                                // Show the modal spinner
                                                modal.spinner(true);

                                                // AJAX Request
                                                API.endpoint('/groups/update?id='+self._properties.id).data(form.val()).execute(function(response){

                                                    // Update the component data
                                                    self._data.record = response.record;

                                                    // Update the group block
                                                    self._component.details.meta.group.text(self._data.record.name);
                                                    if(self._data.record.isDefault){
                                                        if(self._component.details.meta.group.find('.badge').length === 0){
                                                            self._component.details.meta.group.append($(document.createElement('span')).attr({
                                                                'class': 'badge rounded-pill p-2 text-bg-primary ms-2',
                                                                'data-bs-toggle': 'tooltip',
                                                                'data-bs-title': self._builder.Locale.get('Default Group'),
                                                            }).html('<i class="bi bi-check-lg"></i>'));
                                                            new bootstrap.Tooltip(self._component.details.meta.group.find('.badge'));
                                                        }
                                                    } else {
                                                        self._component.details.meta.group.find('.badge').remove();
                                                    }
                                                    self._component.details.meta.description.text(self._data.record.description);

                                                    // Hide the modal
                                                    modal.hide();
                                                },function(xhr, status, error){
                                                    modal.hide();
                                                });
                                            },
                                        }
                                    },
                                    function(form,component){

                                        // Add event listener on the modal submit button
                                        parent.content.footer.submit.click(function(e){
                                            e.preventDefault();
                                            e.stopPropagation();
                                            form.submit();
                                        });

                                        // name
                                        form.add(
                                            'text',
                                            {
                                                name: 'name',
                                                placeholder: self._builder.Locale.get('Enter name'),
                                                value: self._data.record.name,
                                                class: {
                                                    component: 'bg-gray-200 p-3 py-2 rounded-0 border-bottom',
                                                },
                                            },
                                        );

                                        // description
                                        form.add(
                                            'textarea',
                                            {
                                                name: 'description',
                                                placeholder: self._builder.Locale.get('Enter a description here...'),
                                                value: self._data.record.description,
                                                class: {
                                                    component: 'rounded-0',
                                                    input: 'rounded-0 border-0',
                                                },
                                            },
                                            function(input){
                                                input._component.input.addClass('min-vh-20').css('resize','none');
                                            },
                                        );

                                        // isDefault
                                        form.add(
                                            'switch',
                                            {
                                                name: 'isDefault',
                                                label: builder.Locale.get('Default'),
                                                value: self._data.record.isDefault ? true : false,
                                                class: {
                                                    component: 'bg-gray-200 p-3 py-2 rounded-0 border-top',
                                                },
                                            },
                                        );

                                        // Resolve the promise
                                        resolve();
                                    },
                                );
                            } catch (error) {
                                reject(error);
                            }
                        });
                    },
                }
            },
            function(modal,component){
                modal.show();
            },
        );
    }

    tabs(tabs = null){
        if(tabs !== null){
            this._tabs = tabs;
        }
        return this._tabs;
    }
});
