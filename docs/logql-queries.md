# Truy vấn LogQL cho bản demo

Mở Grafana tại `http://localhost:3001`, vào **Explore**, chọn datasource **Loki**.

## 1. Xem log của mọi container

```logql
{job="docker"}
```

## 2. Lọc log WordPress

```logql
{job="docker", container=~".*wordpress.*"}
```

## 3. Tìm dòng có lỗi

```logql
{job="docker"} |= "error"
```

Nếu chưa thấy log mới, kiểm tra trạng thái container bằng `docker compose ps`, rồi xem collector bằng `docker compose logs --tail=100 promtail loki`. Promtail cần Docker đang dùng `json-file` logging driver để tìm log path qua Docker service discovery.
