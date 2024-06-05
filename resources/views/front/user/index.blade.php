<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

   <style>
       .user-list {
           list-style: none;
           padding: 0;
           display: flex;
           flex-wrap: wrap;
           justify-content: flex-start; /* Выравнивание элементов списка по левой стороне */
           align-items: flex-start; /* Выравнивание элементов по верхнему краю */
       }

       .user-list-item {
           width: calc(50% - 10px); /* Ширина элемента будет половиной ширины контейнера, минус отступ между элементами */
           margin-bottom: 20px;
           border: 1px solid #ccc;
           border-radius: 5px;
           padding: 10px;
       }

       .user-list-item img {
           width: 50px;
           height: 50px;
           border-radius: 50%;
           margin-right: 10px;
       }

       .user-list-item h3 {
           margin: 0;
       }

       .user-list-item p {
           margin: 0;
       }

   </style>
</head>
<body class="antialiased">
<a href="{{route('create-user')}}">Создать пользователя</a>
<ul id="user-list" class="user-list">
</ul>

<button id="load-more-btn">Загрузить еще</button>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const userList = document.getElementById('user-list');
        const loadMoreBtn = document.getElementById('load-more-btn');

        let nextPage = '{{route('get-users')}}';
        function loadMoreUsers() {
            fetch(nextPage)
                .then(response => {
                    // Проверяем статус ответа
                    if (!response.ok) {
                        throw new Error('Ошибка запроса к API: ' + response.status);
                    }
                    // Преобразуем ответ в формат JSON
                    return response.json();
                })
                .then(data => {
                    // Перебираем полученные данные и добавляем их в список пользователей
                    data.users.forEach(user => {
                        const listItem = document.createElement('li');
                        listItem.classList.add('user-list-item');
                        const image = document.createElement('img');
                        image.src = user.photo ? user.photo : 'placeholder.png';
                        image.alt = user.name;
                        image.onerror = function() {
                            this.src = '/assets/img/default.jpg';
                        };
                        const name = document.createElement('h3');
                        name.textContent = user.name;
                        const email = document.createElement('p');
                        email.textContent = user.email;
                        const phone = document.createElement('p');
                        phone.textContent = user.phone;
                        const position = document.createElement('p');
                        position.classList.add('position');
                        position.textContent = user.position;
                        listItem.appendChild(image);
                        listItem.appendChild(name);
                        listItem.appendChild(email);
                        listItem.appendChild(phone);
                        listItem.appendChild(position);
                        userList.appendChild(listItem);
                    });
                    nextPage = data.links.next_url;
                })
                .catch(error => {
                    console.error('Ошибка загрузки пользователей:', error);
                });
        }

        loadMoreBtn.addEventListener('click', loadMoreUsers);

        loadMoreUsers();
    });

</script>
</html>
