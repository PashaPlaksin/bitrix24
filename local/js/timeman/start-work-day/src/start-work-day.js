BX.namespace('Otus.Timeman.StartWorkDay');

BX.Otus.Timeman.StartWorkDay = {
    actions: ['open', 'reopen'],

    init: function () {
        if (!BX.CTimeMan) return false;

        const self = this;

        BX.CTimeMan.prototype.QueryPrimary = BX.CTimeMan.prototype.Query;

        BX.CTimeMan.prototype.Query = function (action, data, callback, bForce) {
            console.error('[Timeman Action]:', action);

            if (!self.actions.includes(action)) {
                this.QueryPrimary(action, data, callback, bForce);
                return false;
            }

            const isStart = action === 'open';

            const titleText = isStart ? 'Начало рабочего дня' : 'Продолжение рабочего дня';
            const btnText = isStart ? 'Начать' : 'Продолжить';
            const contentText = isStart
                ? 'Вы собираетесь начать рабочий день. Нажмите "Начать", чтобы подтвердить, или "Отменить", чтобы закрыть окно.'
                : 'Вы собираетесь продолжить рабочий день. Нажмите "Продолжить", чтобы подтвердить, или "Отменить", чтобы закрыть окно.';

            const popup = BX.PopupWindowManager.create('popup', '', {
                className: 'bx-timeman-start-work-popup-window',
                autoHide: true,
                offsetLeft: -60,
                offsetTop: 8,
                overlay: true,
                draggable: false,
                closeByEsc: true,
                closeIcon: { right: '10px', top: '20px' },
                width: 380,
                maxHeight: 300,
                titleBar: titleText,
                content: contentText,
                buttons: [
                    new BX.PopupWindowButton({
                        text: btnText,
                        id: 'start-btn',
                        className: 'ui-btn ui-btn-success',
                        events: {
                            click: function () {
                                BX.CTimeMan.prototype.QueryPrimary(action, data, callback, bForce);
                                popup.close();
                            },
                        },
                    }),
                    new BX.PopupWindowButton({
                        text: 'Отменить',
                        id: 'cancel-btn',
                        className: 'ui-btn ui-btn-light-border',
                        events: {
                            click: function () {
                                popup.close();
                                BX.Otus.Timeman.StartWorkDay.closeTimePopup();
                            },
                        },
                    }),
                ],
            });

            popup.show();
        };
    },

    closeTimePopup: function () {
        const timemanBlock = BX('timeman_main');
        const popupContent = BX('popup-window-content-timeman_main');

        if (timemanBlock) {
            BX.style(timemanBlock, 'display', 'none');
            BX.removeClass(timemanBlock, '--open');
        }

        if (popupContent) {
            const waitButton = BX.findChild(popupContent, { className: 'ui-btn-wait' }, true);
            if (waitButton) {
                BX.removeClass(waitButton, 'ui-btn-wait');
            }
        }
    },
};

BX.ready(function () {
    BX.Otus.Timeman.StartWorkDay.init();
});
