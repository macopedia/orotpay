import $ from "jquery";
import confirmModal from "tpl-loader!../../templates/confirm-modal.html";
import mediator from 'oroui/js/mediator';

class MobileAppConfirmModal {
    static handle(resolvedEventData, options) {
        if (!resolvedEventData.successful) {
            mediator.execute('redirectTo', {url: resolvedEventData.errorUrl}, {redirect: true});
            return;
        }

        const loaderMask = $('.loader-mask');

        let ticks = 1;
        let timeoutRef = null;

        loaderMask.append(confirmModal({
            app_icon: options.app_icon,
            app_alt: options.app_alt,
        }));

        function closeAndRedirect(url) {
            loaderMask.remove(loaderMask.find('.tpay-overlay-confirm-modal'));
            clearTimeout(timeoutRef);
            mediator.execute('redirectTo', {url: url}, {redirect: true})
        }

        function fetchStatus() {
            $.ajax({
                url: resolvedEventData.statusUrl,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    if (result.status === 'full') {
                        closeAndRedirect(resolvedEventData.successUrl);
                    } else if (result.status === 'declined') {
                        closeAndRedirect(resolvedEventData.errorUrl);
                    }
                },
                complete: function () {
                    clearTimeout(timeoutRef);

                    if (ticks < 10) {
                        timeoutRef = setTimeout(fetchStatus, 15000);
                        ticks++;
                    } else {
                        closeAndRedirect(resolvedEventData.errorUrl);
                    }
                }
            })
        }

        fetchStatus();
    }
}

export default MobileAppConfirmModal;
