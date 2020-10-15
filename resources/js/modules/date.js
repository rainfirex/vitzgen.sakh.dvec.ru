export const currentDate = () => {
    const today = new Date();
    return today.getFullYear()+'-'+(addZero(today.getMonth()+1))+'-'+addZero(today.getDate());
};

function addZero(i) {
    if (i < 10) {
        i = "0" + i;
    }
    return i;
}
