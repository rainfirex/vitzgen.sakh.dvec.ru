export const playSound = (filename) => {
    const audio = new Audio('/sounds/'+filename).play();
};
