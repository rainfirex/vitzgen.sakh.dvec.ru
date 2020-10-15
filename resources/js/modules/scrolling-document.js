export const scrollingDoc = {
    isScroll(boolean) {
        if(typeof boolean === "boolean")
            (!boolean) ?  document.body.style.overflow = 'hidden' : document.body.style.overflow = '';
    }
};
