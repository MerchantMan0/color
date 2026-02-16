const API_BASE = "/api";

async function postJson(path, data)
{
	const response = await fetch(`${API_BASE}/${path}`, {
		method: "POST",
		headers: { "Content-Type": "application/json" },
		body: JSON.stringify(data)
	});

	if (!response.ok)
	{
		throw new Error(`HTTP ${response.status}`);
	}

	return response.json();
}

function setMessage(id, text, isError)
{
	const el = document.getElementById(id);
	if (!el) return;
	el.textContent = text;
	el.className = isError ? "message error" : "message";
}

function saveUser(user)
{
	sessionStorage.setItem("userId", String(user.id));
	sessionStorage.setItem("firstName", user.firstName);
	sessionStorage.setItem("lastName", user.lastName);
}

function clearUser()
{
	sessionStorage.removeItem("userId");
	sessionStorage.removeItem("firstName");
	sessionStorage.removeItem("lastName");
}

function getUser()
{
	const userId = Number(sessionStorage.getItem("userId") || "0");
	const firstName = sessionStorage.getItem("firstName") || "";
	const lastName = sessionStorage.getItem("lastName") || "";
	return { userId, firstName, lastName };
}

function requireLogin()
{
	const user = getUser();
	if (!user.userId)
	{
		window.location.href = "index.html";
		return null;
	}
	return user;
}

async function doLogin(event)
{
	event.preventDefault();
	setMessage("login-message", "", false);

	const login = document.getElementById("login").value.trim();
	const password = document.getElementById("password").value.trim();

	if (!login || !password)
	{
		setMessage("login-message", "Enter login and password.", true);
		return;
	}

	try
	{
		const data = await postJson("Login.php", { login, password });
		if (data.error && data.error !== "")
		{
			setMessage("login-message", data.error, true);
			return;
		}

		saveUser({ id: data.id, firstName: data.firstName, lastName: data.lastName });
		window.location.href = "color.html";
	}
	catch (err)
	{
		setMessage("login-message", "Login failed.", true);
	}
}

async function doAddColor(event)
{
	event.preventDefault();
	setMessage("add-message", "", false);

	const user = requireLogin();
	if (!user) return;

	const color = document.getElementById("color").value.trim();
	if (!color)
	{
		setMessage("add-message", "Enter a color.", true);
		return;
	}

	try
	{
		const data = await postJson("AddColor.php", { userId: user.userId, color });
		if (data.error && data.error !== "")
		{
			setMessage("add-message", data.error, true);
			return;
		}
		setMessage("add-message", "Color added.", false);
		document.getElementById("color").value = "";
	}
	catch (err)
	{
		setMessage("add-message", "Add color failed.", true);
	}
}

async function doSearch(event)
{
	event.preventDefault();
	setMessage("search-message", "", false);

	const user = requireLogin();
	if (!user) return;

	const search = document.getElementById("search").value.trim();
	const list = document.getElementById("results-list");
	list.innerHTML = "";

	try
	{
		const data = await postJson("SearchColors.php", { userId: user.userId, search });
		if (data.error && data.error !== "")
		{
			setMessage("search-message", data.error, true);
			return;
		}

		if (!data.results || data.results.length === 0)
		{
			setMessage("search-message", "No results.", false);
			return;
		}

		data.results.forEach((name) =>
		{
			const li = document.createElement("li");
			li.textContent = name;
			list.appendChild(li);
		});
	}
	catch (err)
	{
		setMessage("search-message", "Search failed.", true);
	}
}

function doLogout()
{
	clearUser();
	window.location.href = "index.html";
}

document.addEventListener("DOMContentLoaded", () =>
{
	const loginForm = document.getElementById("login-form");
	if (loginForm) loginForm.addEventListener("submit", doLogin);

	const addForm = document.getElementById("add-form");
	if (addForm) addForm.addEventListener("submit", doAddColor);

	const searchForm = document.getElementById("search-form");
	if (searchForm) searchForm.addEventListener("submit", doSearch);

	const logoutBtn = document.getElementById("logout-btn");
	if (logoutBtn) logoutBtn.addEventListener("click", doLogout);

	const userLabel = document.getElementById("user-name");
	if (userLabel)
	{
		const user = requireLogin();
		if (user)
		{
			userLabel.textContent = `Signed in as ${user.firstName} ${user.lastName}`;
		}
	}
});
