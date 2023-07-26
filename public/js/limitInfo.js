const categoryInput = document.querySelector("#category");
let categoryLimitValue = 0;
let categoryLimitState = 0;
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

const getLimitStateForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limitState/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR:', e)
    }
}

const howMuchLeft = (limit, summary, enteredAmount) => {
    if(limit) {
        const limitCalculation = (Math.round((limit - summary - enteredAmount) * 100) / 100);
        if (limitCalculation >= 0) {
            container
            container.setAttribute("style", "color: #198754;");
        } else {
            container.setAttribute("style", "color: #dc3545;");
        }
        return `${limitCalculation} PLN`;
    } else {
        container.setAttribute("style", "color: none;");
        return "ile chcesz";
    }
}

const getResult = () => {
    if (categoryLimitState) {
        result.textContent = `Limit dla wybranej kategorii wynosi: ${categoryLimitValue} PLN oraz jest aktywny. Dotychczas w kategorii wydałeś: ${categorySummary} PLN. Możesz wydać jeszcze ${howMuchLeft(categoryLimitValue, categorySummary, enteredAmount)}.`;
    } else if (categoryLimitState == 0 && categoryLimitValue == null) {
        result.textContent = `Limit dla wybranej kategorii nie został zdefiniowany.`;

    } else {
        result.textContent = `Limit dla wybranej kategorii wynosi: ${categoryLimitValue} PLN, jednak nie jest aktywny. Możesz wydać jeszcze ile chcesz.`;
    }
}

onload = async () => {
    categoryLimitValue = await getLimitForCategory(categoryInput.value);
    categoryLimitState = await getLimitStateForCategory(categoryInput.value);
    categorySummary = await getSummaryForCategory(categoryInput.value);
    getResult();
};

amountInput.oninput = (event) => {
    enteredAmount = event.target.value;
    getResult();
};

categoryInput.oninput = async (event) => {
    categoryLimitValue = await getLimitForCategory(event.target.value);
    categoryLimitState = await getLimitStateForCategory(categoryInput.value);
    categorySummary = await getSummaryForCategory(event.target.value);
    getResult();
};