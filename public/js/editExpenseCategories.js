const categoryInput = document.querySelector("#categoryName");
const activateLimitBtn = document.querySelector("#activateLimitBtn");
const categoryName = document.querySelectorAll("#selectedCategoryName");
const hiddenInput = document.querySelectorAll("#hiddenInput");
const currentLimitState = document.querySelectorAll("#currentLimitState");
const categoryCard = document.querySelector("#selectedCategoryCard");
let categoryLimitValue;
let categoryLimitState;
let selectedCategoryName;


const getLimitStateForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limitState/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR:', e)
    }
}


const getLimitForCategory = async (category) => {
    try {
        const res = await fetch(`../api/limit/${category}`);
        const data = await res.json();
        return data;
    } catch (e) {
        console.log('ERROR:', e);
    }
}

onload = () => {
    categoryCard.setAttribute("style", "display: none;");
}

categoryInput.oninput = async (event) => {
    selectedCategoryName = event.target.value;
    categoryLimitValue = await getLimitForCategory(event.target.value);
    categoryLimitState = await getLimitStateForCategory(event.target.value);

    if (selectedCategoryName !== "Wybierz kategorię") {
        categoryCard.removeAttribute("style");
        
        for(let i=0; i<categoryName.length; i++) {
            categoryName[i].textContent = `${selectedCategoryName}`;       
        }

        for(let i=0; i<currentLimitState.length; i++) {
            currentLimitState[i].setAttribute('value', `${categoryLimitState}`);
        }
    
        for(let i=0; i<categoryName.length; i++) {
            try {
                hiddenInput[i].setAttribute('value', `${selectedCategoryName}`);
            } catch (e) {
                console.log('ERROR:', e);
            }
        }
    } else {
        categoryCard.setAttribute("style", "display: none;");
    }

    if (categoryLimitState) {
        activateLimitBtn.removeAttribute("class");
        activateLimitBtn.classList.add("btn");
        activateLimitBtn.classList.add("btn-outline-success");
        activateLimitBtn.classList.add("editCategoryBtn");
        activateLimitBtn.disabled = false;
    } else if (!categoryLimitState && categoryLimitValue == null) {
        activateLimitBtn.removeAttribute("class");
        activateLimitBtn.classList.add("btn");
        activateLimitBtn.classList.add("btn-outline-light");
        activateLimitBtn.classList.add("editCategoryBtn");
        activateLimitBtn.disabled = true;
    } else {
        activateLimitBtn.removeAttribute("class");
        activateLimitBtn.classList.add("btn");
        activateLimitBtn.classList.add("btn-outline-danger");
        activateLimitBtn.classList.add("editCategoryBtn");
        activateLimitBtn.disabled = false;
    }

    if (categoryLimitValue >= 0 && categoryLimitValue != null) {
        activateLimitBtn.textContent = `Limit: ${categoryLimitValue} PLN`;
    } else if (categoryLimitValue == null) {
        activateLimitBtn.textContent = 'Limit nie zdefiniowany';
    } else {
        activateLimitBtn.textContent = 'Limit: 0.00 PLN';
    }
};

