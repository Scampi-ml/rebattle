var AimP = new Vue({
    el: '#app',
    data: {
        user: user,
        userBlockId: null,
        userAttackId: null,
        userActionId: null,
        modalItemLoad: false,
        modalItem: {},
        modalUserItem: {},

        userItems: {},
        items: {},
        itemsUsed: {},
        modalItemLoad: false,
        modalItem: {},
        modalUserItem: {},
        modalMode: null,
    },
    methods: {
        selectAction: function (actionId) {
            this.userActionId = actionId;
        },
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
                    self.modalItem = data.item;
                    self.modalUserItem = data.userItem;
                    self.modalItemLoad = true;
                    console.log("data", data);
                }
            });
        },
        closeItemModal: function (opt) {
            $('#modal-item').modal('hide')
            this.modalItem = {};
            this.modalItemLoad = false;
        },
    }
});
$(function() {
    $('.user-hit-container').removeClass("d-none").addClass('animate__animated animate__zoomInDown');
    $('.user-hp-container').addClass('animate__animated animate__shakeX');
    setTimeout(function() {
        $('.user-hit-container').removeClass('animate__animated animate__bounce');
        $('.user-hit-container').addClass('animate__animated animate__zoomOut');
        $('.user-hp').css({width: '20%'});
        $('.user-hp-container').removeClass('animate__animated animate__shakeX');
        setTimeout(function() {
            $('.user-hit-container').removeClass('animate__animated animate__zoomOut');
            $('.user-hit-container').addClass("d-none");
        }, 1000);
    }, 1800);
});