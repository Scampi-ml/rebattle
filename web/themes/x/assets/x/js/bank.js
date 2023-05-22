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
        modalBankItem: {},
        modalMode: null
    },
    methods: {
        showItem: function (opt) {
            var self = this;
            $('#modal-item').modal('show');
            $.request('onShowItem', {
                "data": {
                    "id": typeof(opt.id) !== 'undefined' ? opt.id : null,
                    "uid": typeof(opt.uid) !== 'undefined' ? opt.uid : null,
                    "bid": typeof(opt.bid) !== 'undefined' ? opt.bid : null,
                    "mode": opt.mode
                },
                "success": function(data) {
                    $('.tt').tooltip();
                    console.log("data", data);
                    self.modalItem = data.item;
                    self.modalUserItem = data.userItem;
                    self.modalBankItem = data.bankItem;
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
                    "uid": typeof(opt.uid) !== 'undefined' ? opt.uid : null,
                    "bid": typeof(opt.bid) !== 'undefined' ? opt.bid : null,
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