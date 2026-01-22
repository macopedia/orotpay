import $ from 'jquery';
import _ from "underscore";
import BaseComponent from 'oroui/js/app/components/base/component';

const TpayLogsComponent = BaseComponent.extend({
    initialize: function (options) {

        this.options = _.extend({}, this.options, options);

        this.$el = this.options._sourceElement;

        const $showLogs = this.$el.find('.tpay-button-logs');

        $showLogs.on('click', function(e){
            e.preventDefault();

            const id = this.getAttribute('data-id');
            const state = parseInt($(this).attr('data-hidden'));
            const $container = $('#'+id);

            if (state === 1) {
                $(this).attr('data-hidden', 0);
                $showLogs.html($showLogs.attr('data-hide-label'));
                $container.show();

            } else {
                $(this).attr('data-hidden', 1)
                $showLogs.html($showLogs.attr('data-show-label'));
                $container.hide();
            }
        });
    }
});

export default TpayLogsComponent;
