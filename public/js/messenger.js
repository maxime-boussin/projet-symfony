(function($) {
    "use strict"; // Start of use strict
    $(document).ready(function () {
        $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
    });
    $('#messageSubmit').on('click', function (e) {
        submitMessage();
    });
    $('#messageContent').keypress(function (e) {
        if(e.which === 13) {
            submitMessage();
            return false;
        }
    });
    $('.contact_item').on('click', function(e){
        rebuildConversation($(this).attr('id').substring(8));
    });

    function rebuildConversation(contact){
        $('#messageContact').val(contact);
        let liContact = $('#contact-'+contact);
        let activeContact = $('#active-contact');
        activeContact.find('.img_cont').html(liContact.find('.img_cont').html());
        activeContact.find('.user_info').find('span').html('Discussion avec '+liContact.attr('name'));
        $('#conversation').html('');
        regenConversation(true);
    }
    function regenConversation(fromStart=false) {
        let contact = $('#messageContact').val();
        let date = (fromStart?'2000-01-01':$('.msg_time, .msg_time_send').last().attr('data-text'));
        $.ajax({
            url : window.location.origin+'/messenger/get',
            type : 'POST',
            data: { contact: contact, date: date },
            success : function(data){
                if(data.length>0){
                    data.forEach(function (message) {
                        $('#conversation').append(generateMessage(message));
                    });
                    $('#conversation').scrollTop($('#conversation')[0].scrollHeight);
                }
            }
        });
    }
    function generateMessage(data){
        let isSender = $('#nicknameTopbar').html() === data.sender.nickname;
        let date = new Date(data.date);

        let message= '';
        if($('#message-'+data.id).val() === undefined) {
            message += '<div id="message-' + data.id + '" class="d-flex justify-content-' + (isSender ? 'end' : 'start') + ' mb-4">';
            if (!isSender) {
                message += '<div class="img_cont_msg"><img src="/uploads/' + (data.sender.avatarPath == null ? 'default.png' : data.sender.avatarPath) + '" class="rounded-circle user_img_msg"></div>';
            }
            message += '<div class="msg_cotainer' + (isSender ? '_send' : '') + '">';
            message += data.content;
            message += '<span data-text="' + date.phpformat() + '" class="msg_time' + (isSender ? '_send' : '') + '">' + date.shortformat() + '</span></div>'
            if (isSender) {
                message += '<div class="img_cont_msg"><img src="/uploads/' + (data.sender.avatarPath == null ? 'default.png' : data.sender.avatarPath) + '" class="rounded-circle user_img_msg"></div>';
            }
            message += '</div>'
            if ($('#nicknameTopbar').html() === data.sender.nickname) {

            }
            message += '</div>';
        }
        return message;
    }
    function submitMessage(){
        let content = $('#messageContent').val();
        let contact = $('#messageContact').val();
        $('#messageContent').val('');
        $.ajax({
            url : window.location.origin+'/messenger/send',
            type : 'POST',
            data: { contact: contact, content: content },
            success : function(data){
                regenConversation();
            }
        });
    }
    Date.prototype.phpformat = function() {
        var mm = this.getMonth() + 1; // getMonth() is zero-based
        var dd = this.getDate();
        var h = this.getHours();
        var m = this.getMinutes();
        var s = this.getSeconds();
        var timePart = [(h>9 ? '' : '0') + h,
            (m>9 ? '' : '0') + m,
            (s>9 ? '' : '0') + s
        ].join(':')

        var datePart =  [this.getFullYear(),
            (mm>9 ? '' : '0') + mm,
            (dd>9 ? '' : '0') + dd
        ].join('-');
        return [datePart, timePart].join(' ');
    };

    Date.prototype.shortformat = function() {
        var mm = this.getMonth() + 1; // getMonth() is zero-based
        var dd = this.getDate();
        var h = this.getHours();
        var m = this.getMinutes();
        var timePart = [(h>9 ? '' : '0') + h,
            (m>9 ? '' : '0') + m,
        ].join(':')

        var datePart =  [(dd>9 ? '' : '0') + dd,
            (mm>9 ? '' : '0') + mm,
            this.getFullYear(),
        ].join('/');
        return [datePart, timePart].join(' ');
    };
    setInterval(function () {
        regenConversation();
    },1000)
})(jQuery);