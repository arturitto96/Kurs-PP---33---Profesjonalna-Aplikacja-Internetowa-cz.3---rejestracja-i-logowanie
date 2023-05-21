const modalCategoryInput = document.querySelector("#selectCategory");
const modalLimitInput = document.querySelector("#categoryLimit");
let modalLimit;

const getLimitForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR:', e);
    }
}

onload = async (event) => {
    modalLimit = await getLimitForCategory(modalCategoryInput.value);
    if (modalLimit != 0) {
        modalLimitInput.setAttribute("value", modalLimit);
    }
}

modalCategoryInput.oninput = async (event) => {
    modalLimit = await getLimitForCategory(event.target.value);
    if (modalLimit != 0) {
        modalLimitInput.setAttribute("value", modalLimit);
    }
};