const categoryInput = document.querySelector("#category");
let categoryLimit = 0;
let categorySummary = 0;

const amountInput = document.querySelector("#amount");
let enteredAmount = 0;

const result = document.querySelector("#resultLimitFunctionality");

const container = document.querySelector(".infoBox");

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
        const limitCalculation = (Math.round((limit - summary - enteredAmount) * 100) / 100);
        if (limitCalculation >= 0) {
            container.setAttribute("style", "color: green;");
        } else {
            container.setAttribute("style", "color: red;");
        }
        return `${limitCalculation} PLN`;
    } else {
        container.setAttribute("style", "color: none;");
        return "ile chcesz";
    }
}

const getResult = () => {
result.textContent = `Limit dla wybranej kategorii wynosi: ${categoryLimit} PLN, natomiast dotychczas w kategorii wydałeś: ${categorySummary} PLN. Możesz wydać jeszcze ${howMuchLeft(categoryLimit, categorySummary, enteredAmount)}.`;
}

onload = async () => {
    categoryLimit = await getLimitForCategory(categoryInput.value);
    categorySummary = await getSummaryForCategory(categoryInput.value);
    getResult();
};

amountInput.oninput = (event) => {
    enteredAmount = event.target.value;
    getResult();
};

categoryInput.oninput = async (event) => {
    categoryLimit = await getLimitForCategory(event.target.value);
    categorySummary = await getSummaryForCategory(event.target.value);
    getResult();
};