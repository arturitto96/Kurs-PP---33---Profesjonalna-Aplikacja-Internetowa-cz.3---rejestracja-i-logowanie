const methodInput = document.querySelector("#methodName");
const methodName = document.querySelectorAll("#selectedPaymentName");
const hiddenInput = document.querySelectorAll("#hiddenInput");
const methodCard = document.querySelector("#selectedMethodCard");

methodInput.oninput = (event) => {
    let selectedMethodName = event.target.value;

    if (selectedMethodName !== "Wybierz metodę") {
        methodCard.removeAttribute("style");
        
        for(let i=0; i<methodName.length; i++) {
            methodName[i].textContent = `"${selectedMethodName}"`;       
        }
    
        for(let i=0; i<methodName.length; i++) {
            hiddenInput[i].setAttribute("value", `${selectedMethodName}`);
        }
    } else {
        methodCard.setAttribute("style", "display: none;");
    }
}

onload = () => {
    methodCard.setAttribute("style", "display: none;");
}

