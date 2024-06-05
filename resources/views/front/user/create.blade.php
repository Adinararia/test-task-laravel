<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Добавить пользователя</title>
    {{--    <link rel="stylesheet" href="styles.css">--}}
    <style>
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .user-form {
            width: 400px;
        }

        .user-form h2 {
            margin-bottom: 10px;
        }

        .user-form .form-group {
            margin-bottom: 15px;
        }

        .user-form label {
            display: block;
            font-weight: bold;
        }

        .user-form input[type="text"],
        .user-form input[type="email"],
        .user-form input[type="tel"],
        .user-form input[type="file"] {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .user-form button {
            display: block;
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .user-form button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
<button id="get-token">Получить токен</button>
<a href="{{route('get-users-front')}}">Вернуться на главную страницу</a>


<div class="container">
    <div class="user-form">
        <h2>Добавить нового пользователя</h2>
        <form id="add-user-form">
            <div class="form-group">
                <label for="name">Имя:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <select id="position-select" name="position_id" class="form-select" aria-label="Default select example">
                    <option selected>Choose positions</option>
                </select>
            </div>
            <div class="form-group">
                <label for="photo">Ссылка на фото:</label>
                <input type="file" id="photo" name="photo">
            </div>
            <button type="submit">Добавить</button>
        </form>
    </div>
</div>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const addUserForm = document.getElementById('add-user-form');
        const getTokenButton = document.getElementById('get-token');

        function addUser(event) {
            event.preventDefault();

            const formData = new FormData(addUserForm);
            const token = localStorage.getItem('token');

            fetch('{{route('create-user-api')}}', {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                body: formData
            })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(data => {
                            let fails = '';
                            if (typeof data.fails !== 'undefined' && data.fails !== null) {
                                Object.keys(data.fails).forEach(key => {
                                    // Перебираем значения для каждого ключа
                                    data.fails[key].forEach(messageFail => {
                                        fails += `${messageFail}, `;
                                    });
                                });
                            }

                            throw new Error(data.message + " " + fails + " " + response.status);
                        });
                    }

                    return response.json();
                })
                .then(data => {
                    addUserForm.reset();
                    localStorage.removeItem('token');
                    window.location.href = '{{route('get-users-front')}}';
                })
                .catch(error => {
                    console.error('Ошибка добавления пользователя:', error);
                    alert(error);
                });
        }

        addUserForm.addEventListener('submit', addUser);

        function getToken(event) {
            event.preventDefault();

            fetch('{{route('get-token')}}', {
                method: 'GET',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ошибка добавления пользователя: ' + response.status);
                    }
                    return response.json();
                })
                .then(data => {
                    localStorage.setItem('token', data.token);

                })
                .catch(error => {
                    console.error('Ошибка получения токена:', error);
                });
        }

        getTokenButton.addEventListener('click', getToken);


        fetch('{{route('get-positions')}}')
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                const positionSelect = document.getElementById('position-select');
                data.positions.forEach(position => {
                    var option = document.createElement("option");
                    option.value = position.id;
                    option.text = position.name;
                    positionSelect.appendChild(option);
                });
            })
            .catch(error => {
                console.error('There has been a problem with your fetch operation:', error);
            });

    });

</script>
</body>

</html>
