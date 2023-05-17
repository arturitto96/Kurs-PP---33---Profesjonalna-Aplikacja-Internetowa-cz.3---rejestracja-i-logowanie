const categoryInput = document.querySelector("#category");
let categoryLimit = 0;
let categorySummary = 0;

const amountInput = document.querySelector("#amount");
let enteredAmount = 0;

const result = document.querySelector("#resultLimitFunctionality");

onload = async () => {
    categoryLimit = await getLimitForCategory(input.value);
    categorySummary = await getSummaryForCategory(input.value);
    getResult();
};

amountInput.addEventListener("change", (event) => {
    enteredAmount = event.target.value;
    getResult();
});

categoryInput.onchange = async (event) => {
    categoryLimit = await getLimitForCategory(event.target.value);
    categorySummary = await getSummaryForCategory(event.target.value);
    getResult();
};

const getLimitForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR:', e);
    }
}

const getSummaryForCategory = async (category) => {
try {
    const res = await fetch(`../api/limitSummary/${category}`);
    const data = await res.json();
    return data;
} catch (e) {
    console.log('ERROR:', e);
}
}

const howMuchLeft = (limit, summary, enteredAmount) => {
    if(limit) {
        return `${(Math.round((limit - summary - enteredAmount) * 100) / 100)} PLN`;
    } else {
        return "ile chcesz";
    }
}

const getResult = () => {
result.textContent = `Limit dla wybranej kategorii wynosi: ${categoryLimit} PLN, natomiast dotychczas w kategorii wydałeś: ${categorySummary} PLN. Możesz wydać jeszcze ${howMuchLeft(categoryLimit, categorySummary, enteredAmount)}.`;
}