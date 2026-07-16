const newsContainer = document.getElementById("newsContainer");
const loading = document.getElementById("loadingNews");
const emptyNews = document.getElementById("emptyNews");
const pagination = document.getElementById("pagination");
const searchInput = document.getElementById("searchNews");
const btnSearch = document.getElementById("btnSearch");

let newsData = [];
let filteredNews = [];

const newsPerPage = 6;
let currentPage = 1;

async function loadNews() {

    showLoading();

    try {

        const response = await fetch("api/news.php");

        newsData = await response.json();

        filteredNews = [...newsData];

        hideLoading();

        renderPage(1);

    } catch (error) {

        console.error(error);

        hideLoading();

        emptyNews.style.display = "block";

    }

}

function showLoading() {

    loading.style.display = "block";
    emptyNews.style.display = "none";
    newsContainer.innerHTML = "";

}

function hideLoading() {

    loading.style.display = "none";

}

function renderPage(page) {

    currentPage = page;

    newsContainer.innerHTML = "";

    if (filteredNews.length === 0) {

        emptyNews.style.display = "block";
        pagination.innerHTML = "";

        return;

    }

    emptyNews.style.display = "none";

    const start = (page - 1) * newsPerPage;
    const end = start + newsPerPage;

    const items = filteredNews.slice(start, end);

    items.forEach(news => {

        newsContainer.innerHTML += createCard(news);

    });

    renderPagination();

}

function createCard(news) {

    return `

<div class="col-lg-4 col-md-6 wow fadeInUp mb-4">

    <div class="blog-item">

        <div class="blog-img">

            <img
                src="${news.image}"
                alt="${news.title}">

        </div>

        <div class="blog-title">

            <h3>${news.title}</h3>

            <a
                class="btn"
                href="${news.url}"
                target="_blank">

                +

            </a>

        </div>

        <div class="blog-meta">

            <p>

                <i class="fa fa-calendar-alt"></i>

                ${news.date}

            </p>

            <p>

                <i class="fa fa-tag"></i>

                ${news.category}

            </p>

        </div>

        <div class="blog-text">

            <p>

                ${news.description}

            </p>

        </div>

    </div>

</div>

`;

}

function renderPagination() {

    pagination.innerHTML = "";

    const totalPages = Math.ceil(filteredNews.length / newsPerPage);

    if (totalPages <= 1) return;

    let previousDisabled = currentPage === 1 ? "disabled" : "";

    pagination.innerHTML += `

<li class="page-item ${previousDisabled}">

<a class="page-link" href="#" onclick="changePage(${currentPage - 1})">

Previous

</a>

</li>

`;

    for (let i = 1; i <= totalPages; i++) {

        let active = currentPage === i ? "active" : "";

        pagination.innerHTML += `

<li class="page-item ${active}">

<a class="page-link" href="#" onclick="changePage(${i})">

${i}

</a>

</li>

`;

    }

    let nextDisabled = currentPage === totalPages ? "disabled" : "";

    pagination.innerHTML += `

<li class="page-item ${nextDisabled}">

<a class="page-link" href="#" onclick="changePage(${currentPage + 1})">

Next

</a>

</li>

`;

}

function changePage(page) {

    const totalPages = Math.ceil(filteredNews.length / newsPerPage);

    if (page < 1 || page > totalPages) return;

    renderPage(page);

    window.scrollTo({

        top: 300,

        behavior: "smooth"

    });

}

function searchNews() {

    const keyword = searchInput.value.toLowerCase();

    filteredNews = newsData.filter(news => {

        return (

            news.title.toLowerCase().includes(keyword) ||

            news.description.toLowerCase().includes(keyword) ||

            news.category.toLowerCase().includes(keyword)

        );

    });

    renderPage(1);

}

btnSearch.addEventListener("click", searchNews);

searchInput.addEventListener("keyup", function(e){

    if(e.key === "Enter"){

        searchNews();

    }

});

loadNews();