var AimP = new Vue({
    el: '#app',
    data: {
        user: user,
        modalItemLoad: false,
        modalItem: {},
        modalUserItem: {},
        modalMode: null
    },
    methods: {
        showItem: function (opt) {
            var self = this;
            $('#modal-item').modal('show');
            $.request('onShowItem', {
                "data": {
                    "id": typeof(opt.id) !== 'undefined' ? opt.id : null,
                },
                "success": function(data) {
                    $('.tt').tooltip();
                    console.log("data", data);
                    self.modalItem = data.item;
                    self.modalItemLoad = true;
                    self.modalMode = opt.mode;
                }
            });
        },
        closeItemModal: function (opt) {
            $('#modal-item').modal('hide')
            this.modalItem = {};
            this.modalItemLoad = false;
        }
    }
});