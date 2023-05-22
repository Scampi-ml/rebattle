var AimP = new Vue({
    el: '#app',
    data: {
        user: user,
        userItems: {},
        items: {},
        itemsUsed: {},
        modalItemLoad: false,
        modalItem: {},
        modalUserItem: {},
        modalMode: null,
    },
    methods: {
        showItem: function (opt) {
            var self = this;
            $('#modal-item').modal('show');
            $.request('onShowItem', {
                "data": {
                    "id": opt.id,
                    "uid": opt.uid,
                    "mode": opt.mode,
                },
                "success": function(data) {
                    $('.tt').tooltip();
                    console.log("data", data);
                    self.modalItem = data.item;
                    self.modalUserItem = data.userItem;
                    self.modalItemLoad = true;
                    self.modalMode = opt.mode;
                }
            });
        },
        closeItemModal: function (opt) {
            $('#modal-item').modal('hide')
            this.modalItem = {};
            this.modalItemLoad = false;
        },
        actionItem: function(opt) {
            $.request('onAction', {
                "data": {
                    "action": opt.action,
                    "uid": opt.uid,
                    "mode": opt.mode
                },
                "success": function(data) {
                    if (typeof(data) === 'object' && typeof(data.error) === "string") {
                        $.oc.flashMsg({
                            'text': data.error,
                            'class': 'error',
                            'interval': 5
                        });
                    } else {
                        window.location.reload();
                    }
                }
            });
        }
    }
});