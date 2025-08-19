const getParamFromUri = (param: string) => {
    const url = new URL(window.location.href);
    const icon = url.searchParams.get(param)

    return icon ? icon : '';
}

document.addEventListener('alpine:init', function () {
    Alpine.data(
        'inviteCode',
        () => ({
            code: {},
            getParam() {
                this.code = getParamFromUri('invite_code');
                if (this.code) {
                    const name = this.$refs.name;
                    if (name instanceof HTMLInputElement) {
                        name.focus();
                    }
                } else {
                    const code = this.$refs.code;
                    if (code instanceof HTMLInputElement) {
                        code.focus();
                    }
                }
            }
        })
    )
});