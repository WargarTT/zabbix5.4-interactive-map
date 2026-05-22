class MapResizeService {
    constructor({checkInterval, map}) {
        this.checkInterval = checkInterval || 30000;
        this.intervalId = null;
        this.timeoutId = null;
        this.map = map;
        this.resizeHandler = () => this.scheduleCheck();
    }

    run() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
        }

        window.removeEventListener('resize', this.resizeHandler);
        window.addEventListener('resize', this.resizeHandler);

        this.map.checkSize();
        this.intervalId = setInterval(() => this.map.checkSize(), this.checkInterval);

        /* TODO:
    $(window).resize(function () {
        if (document.readyState === 'complete') setInterval(function () {
            app.imap.map.checkSize();

        }, 1000);
    });
         */
    }

    scheduleCheck() {
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }

        this.timeoutId = setTimeout(() => {
            this.timeoutId = null;
            this.map.checkSize();
        }, 250);
    }
}

export default MapResizeService;
