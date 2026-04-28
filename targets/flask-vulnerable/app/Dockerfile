FROM python:3.9

COPY . /app
WORKDIR /app

RUN pip install --no-cache-dir -r requirements.txt

EXPOSE 5000 5678

CMD python app.py
