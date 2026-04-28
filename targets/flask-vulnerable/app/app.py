"""
Vulnerable Web Application — For Workshop Exercise Only
DO NOT deploy this in production!

Contains intentional security vulnerabilities for learning purposes.
Students should find and fix these issues during the workshop.
"""

import os
import hashlib
import sqlite3
import subprocess
from flask import Flask, request, jsonify, render_template_string

app = Flask(__name__)

# === VULNERABILITY 1: Hardcoded credentials ===
DB_PASSWORD = "supersecret123"
API_KEY = "sk-proj-abc123def456ghi789jkl012mno345pqr678"
ADMIN_PASSWORD = "admin123"

# === VULNERABILITY 2: Weak secret key ===
app.secret_key = "secret"


def get_db():
    conn = sqlite3.connect("app.db")
    conn.row_factory = sqlite3.Row
    return conn


@app.route("/")
def index():
    return jsonify({"status": "running", "version": "1.0.0"})


# === VULNERABILITY 3: SQL Injection ===
@app.route("/api/users")
def get_users():
    username = request.args.get("username", "")
    conn = get_db()
    # Bad: string concatenation in SQL query
    query = "SELECT * FROM users WHERE username = '" + username + "'"
    cursor = conn.execute(query)
    users = [dict(row) for row in cursor.fetchall()]
    conn.close()
    return jsonify(users)


# === VULNERABILITY 4: SQL Injection (login) ===
@app.route("/api/login", methods=["POST"])
def login():
    data = request.get_json()
    username = data.get("username", "")
    password = data.get("password", "")

    conn = get_db()
    # Bad: SQL injection in login
    query = f"SELECT * FROM users WHERE username = '{username}' AND password = '{password}'"
    cursor = conn.execute(query)
    user = cursor.fetchone()
    conn.close()

    if user:
        return jsonify({"message": "Login successful", "user": dict(user)})
    return jsonify({"error": "Invalid credentials"}), 401


# === VULNERABILITY 5: XSS (Cross-Site Scripting) ===
@app.route("/search")
def search():
    q = request.args.get("q", "")
    # Bad: rendering user input directly into HTML without escaping
    html = f"""
    <html>
    <body>
        <h1>Search Results</h1>
        <p>You searched for: {q}</p>
        <p>No results found.</p>
    </body>
    </html>
    """
    return render_template_string(html)


# === VULNERABILITY 6: Command Injection ===
@app.route("/api/ping")
def ping():
    host = request.args.get("host", "")
    # Bad: direct OS command execution with user input
    result = os.popen(f"ping -c 1 {host}").read()
    return jsonify({"output": result})


# === VULNERABILITY 7: Path Traversal ===
@app.route("/api/files")
def get_file():
    filename = request.args.get("name", "")
    # Bad: no path validation, allows ../../etc/passwd
    filepath = os.path.join("/app/uploads", filename)
    try:
        with open(filepath, "r") as f:
            content = f.read()
        return jsonify({"content": content})
    except FileNotFoundError:
        return jsonify({"error": "File not found"}), 404


# === VULNERABILITY 8: Weak password hashing ===
@app.route("/api/register", methods=["POST"])
def register():
    data = request.get_json()
    username = data.get("username", "")
    password = data.get("password", "")
    email = data.get("email", "")

    # Bad: no input validation at all
    # Bad: using MD5 for password hashing (weak, fast, no salt)
    password_hash = hashlib.md5(password.encode()).hexdigest()

    conn = get_db()
    conn.execute(
        "INSERT INTO users (username, password, email) VALUES (?, ?, ?)",
        (username, password_hash, email),
    )
    conn.commit()
    conn.close()

    return jsonify({"message": "User created"})


# === VULNERABILITY 9: Missing authorization ===
@app.route("/api/admin/users")
def admin_users():
    # Bad: no authentication or authorization check
    # Anyone can access admin endpoint
    conn = get_db()
    cursor = conn.execute("SELECT * FROM users")
    users = [dict(row) for row in cursor.fetchall()]
    conn.close()
    return jsonify(users)


@app.route("/api/admin/delete/<int:user_id>", methods=["DELETE"])
def admin_delete_user(user_id):
    # Bad: no auth check, IDOR vulnerability
    conn = get_db()
    conn.execute("DELETE FROM users WHERE id = ?", (user_id,))
    conn.commit()
    conn.close()
    return jsonify({"message": f"User {user_id} deleted"})


# === VULNERABILITY 10: Verbose error messages ===
@app.route("/api/debug")
def debug():
    try:
        # Bad: exposing internal information
        result = 1 / 0
    except Exception as e:
        # Bad: returning full stack trace to user
        import traceback

        return jsonify(
            {
                "error": str(e),
                "traceback": traceback.format_exc(),
                "db_path": os.path.abspath("app.db"),
                "env": dict(os.environ),
            }
        ), 500


# === VULNERABILITY 11: SSRF ===
@app.route("/api/fetch")
def fetch_url():
    import requests

    url = request.args.get("url", "")
    # Bad: fetching arbitrary URLs from user input (SSRF)
    try:
        resp = requests.get(url, timeout=5)
        return jsonify({"status": resp.status_code, "body": resp.text[:1000]})
    except Exception as e:
        return jsonify({"error": str(e)}), 400


# === VULNERABILITY 12: Insecure deserialization ===
@app.route("/api/import", methods=["POST"])
def import_data():
    import pickle
    import base64

    data = request.get_json()
    encoded = data.get("payload", "")
    # Bad: deserializing untrusted data with pickle
    obj = pickle.loads(base64.b64decode(encoded))
    return jsonify({"imported": str(obj)})


if __name__ == "__main__":
    # Bad: debug mode in production
    app.run(host="0.0.0.0", port=5000, debug=True)
