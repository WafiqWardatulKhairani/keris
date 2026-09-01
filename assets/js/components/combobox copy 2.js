// GLOBAL COMBOBOX COMPONENT
const Combobox = {
  init({
    boxId,
    inputId,
    hiddenId = null,
    optionsSelector = ".pk-option",
    onSelect = null,
  }) {
    const combo = document.getElementById(boxId);
    if (!combo) return;

    const input = document.getElementById(inputId);
    const hidden = hiddenId ? document.getElementById(hiddenId) : null;

    const dropdown = combo.querySelector(".pk-combobox-dropdown");

    let current = -1;
    let selected = false;

    const getOptions = () => combo.querySelectorAll(optionsSelector);

    if (!dropdown) {
      console.warn("Combobox dropdown not found:", boxId);
      return;
    }

    const open = () => dropdown.classList.add("open");
    const close = () => dropdown.classList.remove("open");

    const removeActive = () =>
      getOptions().forEach((o) => o.classList.remove("active"));

    const setActive = (i) => {
      const visible = [...getOptions()].filter(
        (o) => o.style.display !== "none",
      );

      removeActive();

      if (visible[i]) {
        visible[i].classList.add("active");
        visible[i].scrollIntoView({ block: "nearest" });
      }
    };

    const filter = (keyword) => {
      getOptions().forEach((o) => {
        o.style.display = o.innerText.toLowerCase().includes(keyword)
          ? "block"
          : "none";
      });
    };

    const select = (option) => {
      input.value = option.innerText;

      if (hidden) hidden.value = option.dataset.value;

      selected = true;

      if (onSelect) {
        onSelect(option.dataset.value, option.innerText);
      }

      close();
    };

    input.addEventListener("focus", () => {
      if (!selected) open();
    });

    input.addEventListener("click", () => {
      if (selected) {
        selected = false;
        input.value = "";

        if (hidden) hidden.value = "";
      }

      open();
    });

    input.addEventListener("input", function () {
      selected = false;

      filter(this.value.toLowerCase());
      open();

      const visible = [...getOptions()].filter(
        (o) => o.style.display !== "none",
      );

      current = -1;

      if (visible.length > 0) {
        current = 0;
        setActive(0);
      }
    });

    input.addEventListener("keydown", (e) => {
      const visible = [...getOptions()].filter(
        (o) => o.style.display !== "none",
      );

      if (e.key === "ArrowDown") {
        e.preventDefault();

        current++;
        if (current >= visible.length) current = 0;

        setActive(current);
      }

      if (e.key === "ArrowUp") {
        e.preventDefault();

        current--;
        if (current < 0) current = visible.length - 1;

        setActive(current);
      }

      if (e.key === "Enter") {
        e.preventDefault();
        e.stopPropagation();

        if (visible[current]) select(visible[current]);
        else if (visible.length === 1) select(visible[0]); // ← ganti if jadi else if
      }

      if (e.key === "Escape") close();
    });

    getOptions().forEach((o) => {
      o.addEventListener("click", function () {
        select(this);
      });
    });

    document.addEventListener("click", function (e) {
      if (!combo.contains(e.target)) close();
    });
  },
};

// ======================================================
// TAG INPUT MULTI SELECT
// ======================================================

const TagInput = {
  init({boxId,inputSelector,hiddenName}){

    const box=document.getElementById(boxId);
    if(!box)return;

    const input=box.querySelector(inputSelector);

    const addTag=(text,value)=>{

      const tag=document.createElement("div");
      tag.className="pk-tag";
      tag.dataset.value=value;

      tag.innerHTML=text+' <span class="pk-tag-remove">×</span>';

      box.insertBefore(tag,input);

      const hidden=document.createElement("input");
      hidden.type="hidden";
      hidden.name=hiddenName;
      hidden.value=value;

      tag.appendChild(hidden);

      tag.querySelector(".pk-tag-remove").onclick=function(){
        tag.remove();
      };
    };

    input.addEventListener("keydown",function(e){

      if(e.key==="Enter"){
        e.preventDefault();

        const text=this.value.trim();
        if(!text)return;

        addTag(text,text);
        this.value="";
      }

    });

  }
};