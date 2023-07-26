const categoryInput = document.querySelector("#categoryName");
const categoryName = document.querySelectorAll("#selectedCategoryName");
const hiddenInput = document.querySelectorAll("#hiddenInput");
const categoryCard = document.querySelector("#selectedCategoryCard");

categoryInput.oninput = (event) => {
    let selectedCategoryName = event.target.value;

    if (selectedCategoryName !== "Wybierz kategorię") {
        categoryCard.removeAttribute("style");
        
        for(let i=0; i<categoryName.length; i++) {
            categoryName[i].textContent = `"${selectedCategoryName}"`;       
        }
    
        for(let i=0; i<categoryName.length; i++) {
            hiddenInput[i].setAttribute("value", `${selectedCategoryName}`);
        }
    } else {
        categoryCard.setAttribute("style", "display: none;");
    }
}

onload = () => {
    categoryCard.setAttribute("style", "display: none;");
}

