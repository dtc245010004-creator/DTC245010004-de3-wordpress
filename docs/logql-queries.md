# Truy vấn LogQL cho bản demo

Mở Grafana tại `http://localhost:3001`, vào **Explore**, chọn datasource **Loki**.

## 1. Xem log của mọi container

```logql
{container=~".+"}
```

## 2. Lọc log WordPress

```logql
{container=~".*-wordpress-[0-9]+"}
```

## 3. Tìm dòng có lỗi

```logql
{container=~".+"} |= "error"
```

Nếu chưa thấy log mới, kiểm tra trạng thái container bằng `docker compose ps`, rồi xem collector bằng `docker compose logs --tail=100 promtail loki`. Promtail cần Docker đang dùng `json-file` logging driver để tìm log path qua Docker service discovery.
