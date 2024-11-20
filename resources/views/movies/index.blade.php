<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie  Search App</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/css/style.css') }}">
</head>
<body>
    <header>
        <nav>
            <div class="logo">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 50" width="150" height="40">
                    <style>
                        .logo-text { font-family: Arial, sans-serif; font-weight: bold; font-size: 24px; }
                        .cine { fill: #3498db; }
                        .search { fill: #ecf0f1; }
                        .magnifier { fill: none; stroke: #ecf0f1; stroke-width: 2; }
                    </style>
                    <text x="10" y="35" class="logo-text cine">Cine</text>
                    <text x="70" y="35" class="logo-text search">Search</text>
                    <circle cx="170" cy="25" r="15" class="magnifier" />
                    <line x1="180" y1="35" x2="190" y2="45" class="magnifier" />
                </svg>
            </div>
        </nav>
    </header>

    <section class="hero">
        <h1>Discover Your Next Favorite Movie</h1>
        <div class="search-container">
            <input type="text" id="movie-search" placeholder="Enter a movie title...">
            <button id="search-button">Search</button>
        </div>
    </section>

    <section id="previous-searches" class="previous-searches">
        <h2>Previous Searches</h2>
        <div id="search-history">
            @foreach($searchHistories as $history)
                <span class="search-item" data-id="{{ $history->id }}">
                    {{ $history->query }}
                    <span class="delete-icon">×</span>
                </span>
            @endforeach
        </div>
        <button id="load-more" class="load-more">Load More</button>
    </section>

    <div id="result-container" class="movie-card" style="display: none;">
        <img id="movie-poster" src="" alt="Movie Poster">
        <div id="movie-info" class="movie-card-content">
            <h2 id="movie-title"></h2>
            <p id="movie-rating"></p>
            <p id="movie-description"></p>
            <a id="imdb-link" href="" target="_blank">View on IMDb</a>
        </div>
    </div>

    <div id="error-message" class="error-message"></div>

    <footer>
        <p>&copy; 2024 CineSearch. Powered by Serper API and IMDb.</p>
    </footer>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

const searchButton = document.getElementById('search-button');
const movieSearch = document.getElementById('movie-search');
const resultContainer = document.getElementById('result-container');
const searchHistory = document.getElementById('search-history');
const loadMoreButton = document.getElementById('load-more');
let isAllDisplayed = false;
let totalSearchItems = 0;

function showErrorMessage(message) {
    const errorMessageContainer = document.getElementById('error-message');
    errorMessageContainer.textContent = message;
    errorMessageContainer.style.display = 'block';
}

function hideErrorMessage() {
    document.getElementById('error-message').style.display = 'none';
}

function searchMovie() {
    const movieTitle = movieSearch.value.trim();

    if (!movieTitle) {
        showErrorMessage("Please enter a movie title to search.");
        return;
    }

    hideErrorMessage();

    $.ajax({
        url: '{{ route("movies.search") }}',
        method: 'POST',
        data: { query: movieTitle },
        success: function(data) {
            if (data.message) {
                showErrorMessage(data.message);
                return;
            }

            $('#movie-title').text(data.title);
            $('#movie-rating').text(`IMDb Rating: ${data.rating} (${data.ratingCount} votes)`);
            $('#movie-description').text(data.snippet);
            $('#imdb-link').attr('href', data.link);
            $('#movie-poster').attr('src', data.imageUrl || 'https://via.placeholder.com/200x300.png?text=No+Image');

            resultContainer.style.display = 'block';
            
            loadSearchHistory();
        },
        error: function(xhr) {
            showErrorMessage(xhr.responseJSON?.error || "An error occurred while fetching the movie data.");
        }
    });
}

function loadSearchHistory() {
    $.get('{{ route("movies.loadMore") }}', function(data) {
        searchHistory.innerHTML = '';
        totalSearchItems = data.length;
        const searchesToShow = isAllDisplayed ? data : data.slice(0, 5);

        searchesToShow.forEach(search => {
            const searchItem = document.createElement('span');
            searchItem.classList.add('search-item');
            searchItem.setAttribute('data-id', search.id);
            
            const textSpan = document.createElement('span');
            textSpan.classList.add('search-text');
            textSpan.textContent = search.query;
            
            const deleteIcon = document.createElement('span');
            deleteIcon.classList.add('delete-icon');
            deleteIcon.textContent = '×';
            
            searchItem.appendChild(textSpan);
            searchItem.appendChild(deleteIcon);
            searchHistory.appendChild(searchItem);
        });

        // Show Load More button only if there are more than 5 items and not all are displayed
        loadMoreButton.style.display = totalSearchItems > 5 && !isAllDisplayed ? 'block' : 'none';
    });
}

$(document).on('click', '.search-item', function(e) {
    if (!$(e.target).hasClass('delete-icon')) {
        const searchText = $(this).find('.search-text').text();
        movieSearch.value = searchText;
        searchMovie();
    }
});

$(document).on('click', '.delete-icon', function(e) {
    e.stopPropagation();
    const searchItem = $(this).parent();
    const searchId = searchItem.data('id');

    $.ajax({
        url: `/history/${searchId}`,
        method: 'DELETE',
        success: function() {
            loadSearchHistory();
        },
        error: function() {
            showErrorMessage('Failed to delete search history');
        }
    });
});

loadMoreButton.addEventListener('click', () => {
    isAllDisplayed = true;
    loadSearchHistory();
});

searchButton.addEventListener('click', searchMovie);
movieSearch.addEventListener('keypress', function(e) {
    if (e.key === 'Enter') searchMovie();
});

// Initial load of search history
loadSearchHistory();

</script>

</body>
</html>