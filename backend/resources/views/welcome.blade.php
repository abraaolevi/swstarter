<h1>SWStarter API</h1>

<section>
	<h2>API Documentation</h2>
	<p><strong>Base URL:</strong> <code>https://localhost:8000/api</code></p>
	<h3>Endpoints</h3>
	<ul>
		<li><strong>People</strong>
			<ul>
				<li>POST <code>/people/search</code> — Search for people by name.<br>Response: <code>{ success, message, data: [people] }</code></li>
				<li>GET <code>/people/{id}</code> — Get details for a person by ID.<br>Response: <code>{ success, message, data: person }</code></li>
			</ul>
		</li>
		<li><strong>Films</strong>
			<ul>
				<li>POST <code>/films/search</code> — Search for films by title.<br>Response: <code>{ success, message, data: [films] }</code></li>
				<li>GET <code>/films/{id}</code> — Get details for a film by ID.<br>Response: <code>{ success, message, data: film }</code></li>
			</ul>
		</li>
		<li><strong>Stats</strong>
			<ul>
				<li>GET <code>/stats</code> — Get statistics about the database.<br>Response: <code>{ success, message, data: stats }</code></li>
				<li>If stats are being calculated: <code>{ success: false, message: 'Stats are being calculated. Please try again in a few moments.', data: null }</code> (HTTP 202)</li>
			</ul>
		</li>
	</ul>
	<h3>Common Headers</h3>
	<ul>
		<li><code>Content-Type: application/json</code></li>
	</ul>
	<h3>Responses</h3>
	<ul>
		<li>200 OK: Success</li>
		<li>202 Accepted: Stats are being calculated</li>
		<li>400 Bad Request: Validation error</li>
		<li>404 Not Found: Resource not found</li>
	</ul>
</section>


