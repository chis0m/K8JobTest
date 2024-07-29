#!/bin/sh

script_dir=$(dirname "$(realpath "${BASH_SOURCE[0]}")")

echo "The script directory is: $script_dir"

#kubectl apply -f "$script_dir/loki-secret.yaml"

kubectl create namespace loki
kubectl create namespace grafana
helm install loki grafana/loki --version 6.7.3 --namespace loki --values "$script_dir/loki.yaml"
helm install promtail grafana/promtail --version 6.16.4 --namespace loki --values "$script_dir/promtail.yaml"
helm install grafana grafana/grafana --version 8.3.6  --namespace grafana --values "$script_dir/grafana.yaml"
